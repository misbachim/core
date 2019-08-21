<?php

use App\Business\Model\Requester;

trait Testable
{
    public function getRequester()
    {
        $requester = new Requester;
        $requester->setTenantId(1000000000);
        $requester->setCompanyId(1900000000);
        $requester->setUserId(1);
        return $requester;
    }

    public function transform($arr)
    {
        $arrT = [];
        foreach ($arr as $field => $val) {
            $chunks = explode('_', $field);
            for ($i = 1; $i < count($chunks); $i++) {
                $chunks[$i] = ucfirst($chunks[$i]);
            }
            $arrT[implode($chunks)] = $val;
        }
        return $arrT;
    }

    public function exclude($arr, $keys)
    {
        foreach ($arr as &$item) {
            foreach ($keys as $key) {
                unset($item[$key]);
            }
        }
        return $arr;
    }

    public function include($arr, $maps)
    {
        foreach ($arr as &$item) {
            foreach ($maps as $key => $val) {
                $item[$key] = $val;
            }
        }

        return $arr;
    }

    public function seedManyObjects($object, $sets, $tableName, $idField)
    {
        foreach (range(1, 10) as $i) {
            foreach ($sets as $field => $randomizer) {
                $object[$field] = $randomizer();
            }
            if ($idField) {
                $object[$idField] = $this->dao->save($object);
            }
            $this->seeInDatabase($tableName, $object);
            array_push($this->objects, $object);

            $objectT = $this->transform($object);
            array_push($this->objectsT, $objectT);

            if ($idField) {
                unset($object[$idField]);
            }
        }
    }

    public function expectManyObjectsSuccess($path, $req, $objects, $msg)
    {
        $this->json('POST', $path, $req)->seeJson([
            'status' => 200,
            'message' => $msg
        ]);

        info('request', array($req));

        info('get content', array(json_decode($this->response->getContent())->data));

        // Verify that their lengths are equal.
        $actualObjects = (array) json_decode($this->response->getContent())->data;
        $this->assertEquals(count($objects), count($actualObjects));

        foreach ($objects as $object) {
            foreach ($object as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function expectManyObjectsEmpty($path, $req, $msg)
    {
        $this->json('POST', $path, $req)->seeJson([
            'status' => 200,
            'message' => $msg,
            'data' => []
        ]);
    }

    public function expectObjectSuccess($path, $req, $struct, $object, $msg)
    {
        $this->json('POST', $path, $req)->seeJson([
            'status' => 200,
            'message' => $msg
        ])->seeJsonStructure(['data' => $struct]);

        foreach ($object as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function expectSaveSuccess(
        $path,
        $req,
        $object,
        $struct,
        $msg,
        $tableName = null,
        $idField = null)
    {
        $this->json('POST', $path, $req)
            ->seeJson([
                'status' => 200,
                'message' => $msg
            ])
            ->seeJsonStructure([
                'data' => $struct
            ]);

        if ($tableName && $idField) {
            $data = json_decode($this->response->getContent())->data;
            $object[$idField] = $data->{$idField};
            $this->seeInDatabase($tableName, $object);

            return $object[$idField];
        }
        return null;
    }

    public function expectUpdateSuccess($path, $req, $object, $struct, $msg, $tableName = null)
    {
        $this->json('POST', $path, $req)
            ->seeJson([
                'status' => 200,
                'message' => $msg
            ])
            ->seeJsonStructure([
                'data' => $struct
            ]);

        if ($tableName) {
            $this->seeInDatabase($tableName, $object);
        }
    }

    public function expectPostSuccess($path, $req, $struct, $msg)
    {
        $this->json('POST', $path, $req)
            ->seeJson([
                'status' => 200,
                'message' => $msg
            ])
            ->seeJsonStructure([
                'data' => $struct
            ]);
    }

    public function expectPostFailure($path, $req, $struct, $msg)
    {
        $this->json('POST', $path, $req)
            ->seeJson([
                'status' => 422,
                'message' => $msg
            ])
            ->seeJsonStructure([
                'data' => $struct
            ]);
    }

    public function newObject($object, $sets, $unsets)
    {
        foreach ($sets as $field => $val) {
            $object[$field] = $val;
        }
        foreach ($unsets as $field) {
            unset($object[$field]);
        }
        return $object;
    }
}
