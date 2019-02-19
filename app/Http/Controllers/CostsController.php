<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Classes\CostTreeUtilClass;


class CostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $utilClass = new CostTreeUtilClass();
        $filters = $request->all();

        $clientCostsData = DB::table('costs')
                                     ->join('projects', 'projects.id', '=', 'costs.project_id')
                                     ->join('clients', 'clients.id', '=', 'projects.client_id')
                                     ->join('cost_types', 'cost_types.id', '=', 'costs.cost_type_id')
                                     ->select(
                                         'clients.id as id',
                                         'clients.name',
                                         DB::raw('SUM(costs.Amount) as amount')
                                       )                 
                                     ->groupby('clients.id');
  
        $projectCostsData = DB::table('costs')
                                     ->join('projects', 'projects.id', '=', 'costs.project_id')
                                     ->join('clients', 'clients.id', '=', 'projects.client_id')
                                     ->join('cost_types', 'cost_types.id', '=', 'costs.cost_type_id')
                                     ->select(
                                         'projects.id as id',
                                         'projects.title',
                                         DB::raw('SUM(costs.Amount) as amount'),
                                         'clients.id as client_id'
                                       )
                                     ->groupby('projects.id');

        if (array_key_exists('clients', $filters)) {
            $clients = $filters['clients'];

            $clientCostsData = $clientCostsData
                                    ->whereIn('clients.id', $clients);
            $projectCostsData = $projectCostsData
                                    ->whereIn('clients.id', $clients);
        }

        if (array_key_exists('projects', $filters)) {
            $projects = $filters['projects'];
            $clientCostsData = $clientCostsData
                                    ->whereIn('projects.id', $projects);
            $projectCostsData = $projectCostsData
                                    ->whereIn('projects.id', $projects);
        }
        $costTypeFilter = [];
        if (array_key_exists('cost_types', $filters)) {
            $costs = $filters['cost_types'];
        
            $clientCostsData = $clientCostsData
                                    ->whereIn('cost_types.id', $costs);
            $projectCostsData = $projectCostsData
                                    ->whereIn('cost_types.id', $costs);

            $ch = $utilClass->getChildrenCostTypes($costs);

            foreach($ch as $c) {
                array_push($costTypeFilter, (int) $c);
            }

            foreach($costs as $c) {
                array_push($costTypeFilter, $c);
            }
        }
        else {
            $clientCostsData = $clientCostsData
                                    ->where('cost_types.parent_cost_type_id');
            $projectCostsData = $projectCostsData
                                    ->where('cost_types.parent_cost_type_id');
        }

        $clientArray = json_decode(json_encode($clientCostsData->get(), true), true);
        $projectArray = json_decode(json_encode($projectCostsData->get(), true), true);
        $tree = $utilClass->makeCostTree($clientArray, $projectArray, $costTypeFilter);
        $answer = json_encode($tree);
        
        return $answer;
    }
}
