<?php
namespace App\Http\Controllers;

use App\Business\Dao\PosStructureDao;
use App\Business\Dao\PosStructureHierarchyDao;
use App\Business\Dao\PersonDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Business\Helper\StringHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class for handling posStructure process
 * @property PosStructureDao posStructureDao
 * @property PosStructureHierarchyDao posStructureHierarchyDao
 * @property Requester requester
 */
class PosStructureController extends Controller
{
    public function __construct(
        Requester $requester,
        PosStructureDao $posStructureDao,
        PosStructureHierarchyDao $posStructureHierarchyDao,
        PersonDao $personDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->posStructureDao = $posStructureDao;
        $this->posStructureHierarchyDao = $posStructureHierarchyDao;
        $this->personDao = $personDao;
    }

    /**
     * Get all posStructures in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required"
        ]);

        $data = $this->posStructureDao->getAll();

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one posStructure based on posStructure id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        $posStructure = (array) $this->posStructureDao->getOne($request->id);
        $posStructure['hierarchy'] = $this->getHierarchy($request->id);

        $resp = new AppResponse($posStructure, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getFlatHierarchy(Request $request)
    {
        $this->validate($request, [
            "posStructureId" => "required",
            "companyId" => "required"
        ]);

        $flatHierarchy = $this->posStructureHierarchyDao->getFlat($request->posStructureId);

        $resp = new AppResponse($flatHierarchy, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save posStructure to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPosStructureRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $posStructure = $this->constructPosStructure($request);
            if ($posStructure['is_primary']) {
                $this->posStructureDao->updateAll([
                    'is_primary' => false,
                    'updated_at' => Carbon::now(),
                    'updated_by' => $this->requester->getUserId()
                ]);
            }
            $posStructure['id'] = $this->posStructureDao->save($posStructure);

            if ($request->hierarchy) {
                $this->posStructureHierarchyDao->delete($posStructure['id']);
                $this->savePosStructureHierarchy($request, $posStructure['id']);
            }

            $data['id'] = $posStructure['id'];
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update posStructure to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkPosStructureRequest($request);

        DB::transaction(function () use (&$request) {
            $posStructure = $this->constructPosStructure($request);
            if ($posStructure['is_primary']) {
                $this->posStructureDao->updateAll([
                    'is_primary' => false,
                    'updated_at' => Carbon::now(),
                    'updated_by' => $this->requester->getUserId()
                ]);
            }
            $this->posStructureDao->update($request->id, $posStructure);

            if ($request->hierarchy) {
                $this->posStructureHierarchyDao->delete($request->id);
                $this->savePosStructureHierarchy($request, $request->id);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "id" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->posStructureHierarchyDao->delete($request->id);
            $this->posStructureDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    public function getParent(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'posStructureId' => 'required',
            'positionCode' => 'required'
        ]);

        $parent = $this->posStructureHierarchyDao->getParent(
            $request->posStructureId,
            $request->positionCode
        );

        $resp = new AppResponse($parent, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function addRoot(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'posStructureId' => 'required|integer|exists:pos_structures,id',
            'positionCode' => 'required|exists:positions,code',
        ]);

        DB::transaction(function () use (&$request) {
            $flatHierarchy = $this->posStructureHierarchyDao->getFlat($request->posStructureId)->toArray();
            if (count($flatHierarchy) > 0) {
                throw new AppException(trans('messages.hierarchyNotEmpty'));
            }

            $this->posStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $request->posStructureId,
                'position_code' => $request->positionCode,
                'parent_position_code' => null
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function addParent(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'posStructureId' => 'required|integer|exists:pos_structures,id',
            'srcPositionCode' => 'required|exists:positions,code|different:dstPositionCode',
            'dstPositionCode' => 'required|exists:positions,code|different:srcPositionCode'
        ]);
        if ($this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->srcPositionCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->dstPositionCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $parent = $this->posStructureHierarchyDao->getParent($request->posStructureId, $request->dstPositionCode);
            $this->posStructureHierarchyDao->removeNode($request->posStructureId, $request->dstPositionCode);

            $this->posStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $request->posStructureId,
                'position_code' => $request->srcPositionCode,
                'parent_position_code' => $parent->code
            ]);
            $this->posStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $request->posStructureId,
                'position_code' => $request->dstPositionCode,
                'parent_position_code' => $request->srcPositionCode
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function addSibling(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'posStructureId' => 'required|integer|exists:pos_structures,id',
            'srcPositionCode' => 'required|exists:positions,code|different:dstPositionCode',
            'dstPositionCode' => 'required|exists:positions,code|different:srcPositionCode'
        ]);
        if ($this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->srcPositionCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->dstPositionCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $parent = $this->posStructureHierarchyDao->getParent($request->posStructureId, $request->dstPositionCode);
            if (! $parent->code) {
                throw new AppException(trans('messages.onlyOneRootNode'));
            }
            $this->posStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $request->posStructureId,
                'position_code' => $request->srcPositionCode,
                'parent_position_code' => $parent->code
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function addChild(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'posStructureId' => 'required|integer|exists:pos_structures,id',
            'srcPositionCode' => 'required|exists:positions,code|different:dstPositionCode',
            'dstPositionCode' => 'required|exists:positions,code|different:srcPositionCode'
        ]);
        if ($this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->srcPositionCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->dstPositionCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $this->posStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $request->posStructureId,
                'position_code' => $request->srcPositionCode,
                'parent_position_code' => $request->dstPositionCode,
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function replaceNode(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'posStructureId' => 'required|integer|exists:pos_structures,id',
            'srcPositionCode' => 'required|exists:positions,code|different:dstPositionCode',
            'dstPositionCode' => 'required|exists:positions,code|different:srcPositionCode'
        ]);
        if ($this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->srcPositionCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->dstPositionCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $parent = $this->posStructureHierarchyDao->getParent($request->posStructureId, $request->dstPositionCode);
            $this->posStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $request->posStructureId,
                'position_code' => $request->srcPositionCode,
                'parent_position_code' => $parent->code,
            ]);
            $this->posStructureHierarchyDao->updateChildren(
                $request->posStructureId,
                $request->dstPositionCode,
                [
                    'parent_position_code' => $request->srcPositionCode
                ]
            );
            $this->posStructureHierarchyDao->removeNode($request->posStructureId, $request->dstPositionCode);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function switchNode(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'posStructureId' => 'required|integer|exists:pos_structures,id',
            'srcPositionCode' => 'required|exists:positions,code|different:dstPositionCode',
            'dstPositionCode' => 'required|exists:positions,code|different:srcPositionCode'
        ]);
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->srcPositionCode)) {
            throw new AppException(trans('messages.sourceNodeNotExists'));
        }
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->dstPositionCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            // Generate a random and unique temporary code.
            $tempCode = '';
            while (true) {
                $tempCode = StringHelper::generateRandomAlnum(20);
                if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $tempCode)) {
                    break;
                }
            }
            $srcParent = $this->posStructureHierarchyDao->getParent($request->posStructureId, $request->srcPositionCode);
            $dstParent = $this->posStructureHierarchyDao->getParent($request->posStructureId, $request->dstPositionCode);

            if ($dstParent->code === $request->srcPositionCode) {
                $this->switchPositions(
                    $request->posStructureId,
                    $tempCode,
                    $request->dstPositionCode,
                    $request->srcPositionCode,
                    $srcParent->code
                );
            } else if ($srcParent->code === $request->dstPositionCode) {
                $this->switchPositions(
                    $request->posStructureId,
                    $tempCode,
                    $request->srcPositionCode,
                    $request->dstPositionCode,
                    $dstParent->code
                );
            } else {
                // Redirect destination position's children to elsewhere.
                $this->posStructureHierarchyDao->updateChildren(
                    $request->posStructureId,
                    $request->dstPositionCode,
                    [
                        'parent_position_code' => $tempCode
                    ]
                );

                // Redirect source and destination positions to each other's parent.
                $this->posStructureHierarchyDao->updateNode(
                    $request->posStructureId,
                    $request->dstPositionCode,
                    [
                        'parent_position_code' => $srcParent->code
                    ]
                );
                $this->posStructureHierarchyDao->updateNode(
                    $request->posStructureId,
                    $request->srcPositionCode,
                    [
                        'parent_position_code' => $dstParent->code
                    ]
                );

                // Redirect source position's children to destination position.
                $this->posStructureHierarchyDao->updateChildren(
                    $request->posStructureId,
                    $request->srcPositionCode,
                    [
                        'parent_position_code' => $request->dstPositionCode
                    ]
                );
                // Redirect destination position's children to source position.
                $this->posStructureHierarchyDao->updateChildren(
                    $request->posStructureId,
                    $tempCode,
                    [
                        'parent_position_code' => $request->srcPositionCode
                    ]
                );
            }
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    private function switchPositions($posStructureId, $tempCode, $childCode, $parentCode, $grandparentCode)
    {
        $this->posStructureHierarchyDao->updateNode(
            $posStructureId,
            $childCode,
            [
                'parent_position_code' => $grandparentCode
            ]
        );
        $this->posStructureHierarchyDao->updateChildren(
            $posStructureId,
            $parentCode,
            [
                'parent_position_code' => $tempCode
            ]
        );
        $this->posStructureHierarchyDao->updateChildren(
            $posStructureId,
            $childCode,
            [
                'parent_position_code' => $parentCode
            ]
        );
        $this->posStructureHierarchyDao->updateNode(
            $posStructureId,
            $parentCode,
            [
                'parent_position_code' => $childCode
            ]
        );
        $this->posStructureHierarchyDao->updateChildren(
            $posStructureId,
            $tempCode,
            [
                'parent_position_code' => $childCode
            ]
        );
    }

    public function removeNode(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'posStructureId' => 'required',
            'positionCode' => 'required'
        ]);
        if (! $this->posStructureHierarchyDao->nodeExists($request->posStructureId, $request->positionCode)) {
            throw new AppException(trans('messages.nodeNotExists'));
        }

        $parent = $this->posStructureHierarchyDao->getParent($request->posStructureId, $request->positionCode);
        if (! $parent->code && $this->posStructureHierarchyDao->getChildrenCount($request->posStructureId, $request->positionCode) > 1) {
            throw new AppException(trans('messages.rootHasMultipleChildren'));
        }

        DB::transaction(function () use (&$request, &$parent) {
            $this->posStructureHierarchyDao->updateChildren(
                $request->posStructureId,
                $request->positionCode,
                [
                    'parent_position_code' => $parent->code
                ]
            );
            $this->posStructureHierarchyDao->removeNode($request->posStructureId, $request->positionCode);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    /**
     * Validate save/update posStructure request.
     * @param Request $request
     */
    private function checkPosStructureRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'name' => 'required|max:50',
            'description' => 'present|max:255',
            'isPrimary' => 'required|boolean',
            'hierarchy' => 'nullable|present|json'
        ]);
    }

    /**
     * Construct a posStructure object (array).
     * @param Request $request
     * @return array
     */
    private function constructPosStructure(Request $request)
    {
        $posStructure = [
            "tenant_id"   => $this->requester->getTenantId(),
            "company_id"  => $request->companyId,
            "eff_begin"   => $request->effBegin,
            "eff_end"     => $request->effEnd,
            "name"        => $request->name,
            "description" => $request->description,
            "is_primary"  => $request->isPrimary
        ];
        return $posStructure;
    }

    /**
     * Get structure hierarchy for position
     * @param posStructureId
     * @return array
     */
    private function getHierarchy($posStructureId)
    {
        $data = $this->posStructureHierarchyDao->getRecursive($posStructureId);

        // Prepare mapping of (code => node).
        $nodes = [];
        foreach ($data as $datum) {
            $positionCount = count($this->personDao->getAllByPosition($datum->code));
            $nodes[$datum->code] = [
                'code' => $datum->code,
                'parentCode' => $datum->parentCode,
                'name' => $datum->name,
                'unit' => $datum->unit,
                'positionCount' => $positionCount
            ];
        }

        foreach (array_reverse($data) as $datum) {
            // Return root node.
            if (! $datum->parentCode) {
                // Reverse children array of node due to the algorithm used.
                if (array_key_exists('children', $nodes[$datum->code])) {
                    $nodes[$datum->code]['children'] = array_reverse($nodes[$datum->code]['children']);
                }
                return $nodes[$datum->code];
            }
            // Initialize children array of parent node.
            if (! array_key_exists('children', $nodes[$datum->parentCode])) {
                $nodes[$datum->parentCode]['children'] = [];
            }
            // Reverse children array of child node due to the algorithm used.
            if (array_key_exists('children', $nodes[$datum->code])) {
                $nodes[$datum->code]['children'] = array_reverse($nodes[$datum->code]['children']);
            }
            // Move node to parent's children.
            array_push($nodes[$datum->parentCode]['children'], $nodes[$datum->code]);
            unset($nodes[$datum->code]);
        }
        return count($nodes) === 0 ? null : $nodes;
    }

    /**
     * Save posStructure's hierarchy.
     * @param Request $request
     * @param $posStructureId
     */
    private function savePosStructureHierarchy(Request &$request, $posStructureId)
    {
        if (! $request->hierarchy) {
            return;
        }
        $hierarchy = json_decode($request->hierarchy);
        $node = $hierarchy; // copy into working hierarchy
        $this->validateNode($request, $node); // validate root node

        // Record walks done by depth-first algorithm.
        $walkMemory = [
            [
                'childNo' => 0,
                'node' => $node
            ]
        ];
        // Flat data to be stored into a table.
        $data = [
            [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'pos_structure_id' => $posStructureId,
                'position_code' => $node->id,
                'parent_position_code' => null
            ]
        ];

        while (true) {
            // Go deeper if children exist.
            if (property_exists($node, 'children') && $node->children) {
                // Record new step.
                array_push($walkMemory, [
                    'childNo' => 0,
                    'node' => $node->children[0]
                ]);
                $this->validateNode($request, $node);
                $this->addDatum($data, $posStructureId, $node, 0);
                $node = $node->children[0]; // replace next examined node
            } else {
                $lastIdx = count($walkMemory)-1;
                if ($lastIdx === 0) break; // exit on root node

                $last = $walkMemory[$lastIdx];
                $last['childNo'] += 1; // increment pointer for next sibling
                $parent = $walkMemory[$lastIdx-1];

                // Go shallower if all children have been examined.
                if ($last['childNo'] >= count($parent['node']->children)) {
                    array_pop($walkMemory);
                    $node = $parent['node'];
                    $node->children = null; // remove children from working hierarchy
                } else {
                    $last['node'] = $parent['node']->children[$last['childNo']]; // get next sibling
                    $walkMemory[$lastIdx] = $last; // update last node
                    $this->validateNode($request, $last['node']);
                    $this->addDatum($data, $posStructureId, $parent['node'], $last['childNo']);
                    $node = $last['node'];
                }
            }
        }

        $this->posStructureHierarchyDao->save($data);
    }

    private function validateNode(&$request, $node)
    {
        $request->merge(['node' => (array) $node]);
        $this->validate($request, ['node.id' => 'required|exists:positions,code']);
    }

    private function addDatum(&$data, $id, $node, $childNo)
    {
        array_push($data, [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'pos_structure_id' => $id,
            'position_code' => $node->children[$childNo]->id,
            'parent_position_code' => $node->id
        ]);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'searchQuery' => 'present|string|max:50',
            'pageInfo' => 'required|array'
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $data = $this->posStructureDao->search($request->searchQuery, $offset, $limit);

        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }
}
