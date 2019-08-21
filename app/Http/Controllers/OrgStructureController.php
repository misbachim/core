<?php
namespace App\Http\Controllers;

use App\Business\Dao\OrgStructureDao;
use App\Business\Dao\OrgStructureHierarchyDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PositionDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class for handling orgStructure process
 */
class OrgStructureController extends Controller
{
    public function __construct(
        Requester $requester,
        PersonDao $personDao,
        PositionDao $positionDao,
        OrgStructureDao $orgStructureDao,
        OrgStructureHierarchyDao $orgStructureHierarchyDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->personDao = $personDao;
        $this->positionDao = $positionDao;
        $this->orgStructureDao = $orgStructureDao;
        $this->orgStructureHierarchyDao = $orgStructureHierarchyDao;
        $this->orgStructureFields = array('effBegin', 'effEnd', 'name',
            'description', 'isPrimary');
    }

    /**
     * Get all orgStructures in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);
        $data = $this->orgStructureDao->getAll(
            $this->requester->getTenantId(),
            $request->companyId
        );

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Active orgStructures in one company
     */
    public function getAllActive(Request $request){
        $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);

        $data = $this->orgStructureDao->getAllActive($offset, $pageLimit);

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->orgStructureDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get all Inactive orgStructures in one company
     */
    public function getAllInActive(Request $request){
        $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);

        $data = $this->orgStructureDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->orgStructureDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }


    /**
     * Get one orgStructure based on orgStructure id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        $orgStructure = $this->orgStructureDao->getOne(
            $this->requester->getTenantId(),
            $request->companyId,
            $request->id
        );

        $data = array();
        if (count($orgStructure) > 0) {
            $data['id'] = $orgStructure->id;
            foreach ($this->orgStructureFields as $field) {
                $data[$field] = $orgStructure->$field;
            }
            $data['hierarchy'] = $this->getHierarchy(
                $request->id
            );
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getFlatHierarchy(Request $request)
    {
        $this->validate($request, [
            "orgStructureId" => "required",
            "companyId" => "required"
        ]);

        $flatHierarchy = $this->orgStructureHierarchyDao->getFlat($request->orgStructureId);

        $resp = new AppResponse($flatHierarchy, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save orgStructure to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkOrgStructureRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $orgStructure = $this->constructOrgStructure($request);
            if ($orgStructure['is_primary']) {
                $this->orgStructureDao->updateAll([
                    'is_primary' => false,
                    'updated_at' => Carbon::now(),
                    'updated_by' => $this->requester->getUserId()
                ]);
            }
            $orgStructure['id'] = $this->orgStructureDao->save($orgStructure);

            if ($request->hierarchy) {
                $this->orgStructureHierarchyDao->delete($orgStructure['id']);
                $this->saveOrgStructureHierarchy($request, $orgStructure['id']);
            }

            $data['id'] = $orgStructure['id'];
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update orgStructure to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkOrgStructureRequest($request);

        DB::transaction(function () use (&$request) {
            $orgStructure = $this->constructOrgStructure($request);
            if ($orgStructure['is_primary']) {
                $this->orgStructureDao->updateAll([
                    'is_primary' => false,
                    'updated_at' => Carbon::now(),
                    'updated_by' => $this->requester->getUserId()
                ]);
            }
            $orgStructure['id'] = $request->id;
            $this->orgStructureDao->update(
                $this->requester->getTenantId(),
                $request->companyId,
                $request->id,
                $orgStructure
            );

            if ($request->hierarchy) {
                $this->orgStructureHierarchyDao->delete($request->id);
                $this->saveOrgStructureHierarchy($request, $request->id);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete orgStructure from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "id" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->orgStructureHierarchyDao->delete($request->id);
            $this->orgStructureDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    public function getParent(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'orgStructureId' => 'required',
            'unitCode' => 'required'
        ]);

        $parent = $this->orgStructureHierarchyDao->getParent(
            $request->orgStructureId,
            $request->unitCode
        );

        $resp = new AppResponse($parent, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function addRoot(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'orgStructureId' => 'required|integer|exists:org_structures,id',
            'unitCode' => 'required|exists:units,code',
        ]);

        DB::transaction(function () use (&$request) {
            $flatHierarchy = $this->orgStructureHierarchyDao->getFlat($request->orgStructureId)->toArray();
            if (count($flatHierarchy) > 0) {
                throw new AppException(trans('messages.hierarchyNotEmpty'));
            }

            $this->orgStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'org_structure_id' => $request->orgStructureId,
                'unit_code' => $request->unitCode,
                'parent_unit_code' => null
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function addParent(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'orgStructureId' => 'required|integer|exists:org_structures,id',
            'srcUnitCode' => 'required|exists:units,code|different:dstUnitCode',
            'dstUnitCode' => 'required|exists:units,code|different:srcUnitCode'
        ]);
        if ($this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->srcUnitCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->dstUnitCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $parent = $this->orgStructureHierarchyDao->getParent($request->orgStructureId, $request->dstUnitCode);
            $this->orgStructureHierarchyDao->removeNode($request->orgStructureId, $request->dstUnitCode);

            $this->orgStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'org_structure_id' => $request->orgStructureId,
                'unit_code' => $request->srcUnitCode,
                'parent_unit_code' => $parent->code
            ]);
            $this->orgStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'org_structure_id' => $request->orgStructureId,
                'unit_code' => $request->dstUnitCode,
                'parent_unit_code' => $request->srcUnitCode
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function addSibling(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'orgStructureId' => 'required|integer|exists:org_structures,id',
            'srcUnitCode' => 'required|exists:units,code|different:dstUnitCode',
            'dstUnitCode' => 'required|exists:units,code|different:srcUnitCode'
        ]);
        if ($this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->srcUnitCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->dstUnitCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $parent = $this->orgStructureHierarchyDao->getParent($request->orgStructureId, $request->dstUnitCode);
            if (! $parent->code) {
                throw new AppException(trans('messages.onlyOneRootNode'));
            }
            $this->orgStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'org_structure_id' => $request->orgStructureId,
                'unit_code' => $request->srcUnitCode,
                'parent_unit_code' => $parent->code
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function addChild(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'orgStructureId' => 'required|integer|exists:org_structures,id',
            'srcUnitCode' => 'required|exists:units,code|different:dstUnitCode',
            'dstUnitCode' => 'required|exists:units,code|different:srcUnitCode'
        ]);
        if ($this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->srcUnitCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->dstUnitCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $this->orgStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'org_structure_id' => $request->orgStructureId,
                'unit_code' => $request->srcUnitCode,
                'parent_unit_code' => $request->dstUnitCode,
            ]);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function replaceNode(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'orgStructureId' => 'required|integer|exists:org_structures,id',
            'srcUnitCode' => 'required|exists:units,code|different:dstUnitCode',
            'dstUnitCode' => 'required|exists:units,code|different:srcUnitCode'
        ]);
        if ($this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->srcUnitCode)) {
            throw new AppException(trans('messages.sourceNodeExists'));
        }
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->dstUnitCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            $parent = $this->orgStructureHierarchyDao->getParent($request->orgStructureId, $request->dstUnitCode);
            $this->orgStructureHierarchyDao->save([
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'org_structure_id' => $request->orgStructureId,
                'unit_code' => $request->srcUnitCode,
                'parent_unit_code' => $parent->code,
            ]);
            $this->orgStructureHierarchyDao->updateChildren(
                $request->orgStructureId,
                $request->dstUnitCode,
                [
                    'parent_unit_code' => $request->srcUnitCode
                ]
            );
            $this->orgStructureHierarchyDao->removeNode($request->orgStructureId, $request->dstUnitCode);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function switchNode(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'orgStructureId' => 'required|integer|exists:org_structures,id',
            'srcUnitCode' => 'required|exists:units,code|different:dstUnitCode',
            'dstUnitCode' => 'required|exists:units,code|different:srcUnitCode'
        ]);
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->srcUnitCode)) {
            throw new AppException(trans('messages.sourceNodeNotExists'));
        }
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->dstUnitCode)) {
            throw new AppException(trans('messages.destinationNodeNotExists'));
        }

        DB::transaction(function () use (&$request) {
            // Generate a random and unique temporary code.
            $tempCode = '';
            while (true) {
                $tempCode = StringHelper::generateRandomAlnum(20);
                if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $tempCode)) {
                    break;
                }
            }
            $srcParent = $this->orgStructureHierarchyDao->getParent($request->orgStructureId, $request->srcUnitCode);
            $dstParent = $this->orgStructureHierarchyDao->getParent($request->orgStructureId, $request->dstUnitCode);

            if ($dstParent->code === $request->srcUnitCode) {
                $this->switchUnits(
                    $request->orgStructureId,
                    $tempCode,
                    $request->dstUnitCode,
                    $request->srcUnitCode,
                    $srcParent->code
                );
            } else if ($srcParent->code === $request->dstUnitCode) {
                $this->switchUnits(
                    $request->orgStructureId,
                    $tempCode,
                    $request->srcUnitCode,
                    $request->dstUnitCode,
                    $dstParent->code
                );
            } else {
                // Redirect destination position's children to elsewhere.
                $this->orgStructureHierarchyDao->updateChildren(
                    $request->orgStructureId,
                    $request->dstUnitCode,
                    [
                        'parent_unit_code' => $tempCode
                    ]
                );

                // Redirect source and destination positions to each other's parent.
                $this->orgStructureHierarchyDao->updateNode(
                    $request->orgStructureId,
                    $request->dstUnitCode,
                    [
                        'parent_unit_code' => $srcParent->code
                    ]
                );
                $this->orgStructureHierarchyDao->updateNode(
                    $request->orgStructureId,
                    $request->srcUnitCode,
                    [
                        'parent_unit_code' => $dstParent->code
                    ]
                );

                // Redirect source position's children to destination position.
                $this->orgStructureHierarchyDao->updateChildren(
                    $request->orgStructureId,
                    $request->srcUnitCode,
                    [
                        'parent_unit_code' => $request->dstUnitCode
                    ]
                );
                // Redirect destination position's children to source position.
                $this->orgStructureHierarchyDao->updateChildren(
                    $request->orgStructureId,
                    $tempCode,
                    [
                        'parent_unit_code' => $request->srcUnitCode
                    ]
                );
            }
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    private function switchUnits($orgStructureId, $tempCode, $childCode, $parentCode, $grandparentCode)
    {
        $this->orgStructureHierarchyDao->updateNode(
            $orgStructureId,
            $childCode,
            [
                'parent_unit_code' => $grandparentCode
            ]
        );
        $this->orgStructureHierarchyDao->updateChildren(
            $orgStructureId,
            $parentCode,
            [
                'parent_unit_code' => $tempCode
            ]
        );
        $this->orgStructureHierarchyDao->updateChildren(
            $orgStructureId,
            $childCode,
            [
                'parent_unit_code' => $parentCode
            ]
        );
        $this->orgStructureHierarchyDao->updateNode(
            $orgStructureId,
            $parentCode,
            [
                'parent_unit_code' => $childCode
            ]
        );
        $this->orgStructureHierarchyDao->updateChildren(
            $orgStructureId,
            $tempCode,
            [
                'parent_unit_code' => $childCode
            ]
        );
    }

    public function removeNode(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'orgStructureId' => 'required',
            'unitCode' => 'required'
        ]);
        if (! $this->orgStructureHierarchyDao->nodeExists($request->orgStructureId, $request->unitCode)) {
            throw new AppException(trans('messages.nodeNotExists'));
        }

        $parent = $this->orgStructureHierarchyDao->getParent($request->orgStructureId, $request->unitCode);
        if (! $parent->code && $this->orgStructureHierarchyDao->getChildrenCount($request->orgStructureId, $request->unitCode) > 1) {
            throw new AppException(trans('messages.rootHasMultipleChildren'));
        }

        DB::transaction(function () use (&$request, &$parent) {
            $this->orgStructureHierarchyDao->updateChildren(
                $request->orgStructureId,
                $request->unitCode,
                [
                    'parent_unit_code' => $parent->code
                ]
            );
            $this->orgStructureHierarchyDao->removeNode($request->orgStructureId, $request->unitCode);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    /**
     * Validate save/update orgStructure request.
     * @param request
     */
    private function checkOrgStructureRequest(Request $request)
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
     * Construct a orgStructure object (array).
     * @param request
     */
    private function constructOrgStructure(Request $request)
    {
        $orgStructure = [
            "tenant_id"   => $this->requester->getTenantId(),
            "company_id"  => $request->companyId,
            "eff_begin"   => $request->effBegin,
            "eff_end"     => $request->effEnd,
            "name"        => $request->name,
            "description" => $request->description,
            "is_primary"  => $request->isPrimary
        ];
        return $orgStructure;
    }

    /**
     * Get structure hierarchy for organization
     * @param companyId, orgStructureId
     */
    private function getHierarchy($orgStructureId)
    {
        $data = $this->orgStructureHierarchyDao->getRecursive(
            $this->requester->getTenantId(),
            $this->requester->getCompanyId(),
            $orgStructureId
        );

        // Prepare mapping of (code => node).
        $nodes = [];

        $collection = collect($data);

        $data = $collection->unique('code')->toArray();

        foreach ($data as $datum) {
            $employees=$this->personDao->getAllByUnit($datum->code);
            $positions=$this->positionDao->getQuantityByUnit($datum->code);
            $nodes[$datum->code] = [
                'employees'=>count($employees),
                'positions'=>count($positions),
                'code' => $datum->code,
                'parentCode' => $datum->parentCode,
                'name' => $datum->name,
                'isHead' => $datum->hou,
                'unitType' => $datum->houUnitTypeName,
                'firstName' => $datum->houFirstName,
                'lastName' => $datum->houLastName,
                'photo' => $datum->houPhoto,
                'position' => $datum->houPositionName,
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
     * Save orgStructure's hierarchy.
     * @param request, orgStructure
     */
    private function saveOrgStructureHierarchy(Request $request, $orgStructureId)
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
                'org_structure_id' => $orgStructureId,
                'unit_code' => $node->id,
                'parent_unit_code' => null
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
                $this->addDatum($data, $orgStructureId, $node, 0);
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
                    $this->addDatum($data, $orgStructureId, $parent['node'], $last['childNo']);
                    $node = $last['node'];
                }
            }
        }

        $this->orgStructureHierarchyDao->save($data);
//        if ($request->has('orgStructureHierarchy')) {
//            $data = array();
//            for ($i=0; $i < count($request->orgStructureHierarchy); $i++) {
//                $this->validate($request, [
//                    'orgStructureHierarchy.'.$i.'.unitCode' => 'required',
//                    'orgStructureHierarchy.'.$i.'.parentCode' => 'required'
//                ]);
//
//                $parentCode = $request->orgStructureHierarchy[$i]['parentCode'];
//                array_push($data, [
//                    'tenant_id' => $this->requester->getTenantId(),
//                    'company_id' => $request->companyId,
//                    'org_structure_id' => $orgStructure['id'],
//                    'unit_code' => $request->orgStructureHierarchy[$i]['unitCode'],
//                    'parent_unit_code' => ($parentCode === 0) ? null : $parentCode
//                ]);
//            }
//            $this->orgStructureHierarchyDao->save($data);
//        }
    }

    private function validateNode(&$request, $node)
    {
        $request->merge(['node' => (array) $node]);
        $this->validate($request, ['node.id' => 'required|exists:units,code']);
    }

    private function addDatum(&$data, $id, $node, $childNo)
    {
        array_push($data, [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'org_structure_id' => $id,
            'unit_code' => $node->children[$childNo]->id,
            'parent_unit_code' => $node->id
        ]);
    }
}
