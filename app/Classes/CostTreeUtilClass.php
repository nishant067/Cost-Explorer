<?php

namespace App\Classes;
use Illuminate\Support\Facades\DB;


class CostTreeUtilClass {

    public function buildTree(array $elements, $parentId) {
        $branch = array();
    
        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = CostTreeUtilClass::buildTree($elements, $element['id']);
                if ($children) {
                    $element['breakdown'] = $children;
                }
                else {
                    $element['breakdown'] = [];
                }

                unset($element['parent']);
                $branch[] = $element;
            }
        }
    
        return $branch;
    }

    public function makeCostTree(array $clientData, array $projectData, array $filter = []) {
        $branch = array();

        foreach($clientData as $client) {
            $client['breakdown'] = [];
            $branch[]=$client;
        }

        foreach($projectData as $project) {
            $clientID = $project['client_id'];
            unset($project['client_id']);

            $projectID = $project['id'];
            
            $costsData = DB::table('costs')
                             ->join('projects', 'projects.id', '=', 'costs.project_id')
                             ->join('clients', 'clients.id', '=', 'projects.client_id')
                             ->join('cost_types', 'cost_types.id', '=', 'costs.cost_type_id')
                             ->select(
                                 'cost_types.id as id',
                                 'cost_types.name as name',
                                 'costs.amount as amount',
                                 'cost_types.parent_cost_type_id as parent'
                                )
                             ->where('clients.id', '=', $clientID)
                             ->where('projects.id', '=', $projectID);
            $parentId = 0;
            if (!empty($filter)) {
                $costsData = $costsData
                                ->whereIn('cost_types.id', $filter);
                $parentId = min($filter);
                $parentId = CostTreeUtilClass::getParent(min($filter));
            }
            
            $costsArray = json_decode(json_encode($costsData->get(), true), true);
            $tree = CostTreeUtilClass::buildTree($costsArray, $parentId);
            $project['breakdown'] = $tree;
            

            $index = 0;
            $len = count($branch);
            for ($i = 0; $i < $len; $i++) {
                if ($branch[$i]['id'] == $clientID) {
                    $index = $i;
                    break;
                }
            }

            array_push($branch[$index]['breakdown'], $project);

        }

        return $branch;
    }

    public function getChildrenCostTypes(array $costTypes) {
        $filters = array();
        
        foreach($costTypes as $c) {
            $children = CostTreeUtilClass::getChildren($c);
            if ($children[0] != null) {
                foreach($children as $ch) {
                    array_push($filters, $ch);
                }
            }
            
        }
        return $filters;
    }

    public function getChildren($c) {
        $children = DB::select(
            "SELECT GROUP_CONCAT(lv SEPARATOR ',') as outcome
             FROM (
                SELECT @pv:=(
                    SELECT GROUP_CONCAT(cost_types.ID SEPARATOR ',') 
                    FROM cost_types 
                    WHERE cost_types.Parent_Cost_Type_ID IN (@pv)) AS lv 
                    FROM cost_types
                JOIN 
                (
                    SELECT @pv:=?)tmp
                    WHERE cost_types.Parent_Cost_Type_ID IN (@pv)) a", [$c]
        );
        $result = ($children[0]->outcome);
        $result = explode (",", $result);
        
        return $result;
    }

    public function getParent($c) {
        $parent = DB::table('cost_types')
                        ->select('cost_types.parent_cost_type_id as id')
                        ->where('cost_types.id', $c)
                        ->get();
        $id = $parent[0]->id;
        
        if (isset($id)) {
            return $id;
        }
        else {
            return 0;
        }
    }
}