<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Niche;
use Illuminate\Http\Request;
use App\Filters\V1\NicheFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreNicheRequest;
use App\Http\Requests\V1\UpdateNicheRequest;
use App\Http\Resources\V1\Niche\NicheResource;
use App\Http\Resources\V1\Niche\NicheCollection;

class NicheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $filter = new NicheFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $niches = Niche::where($AndFilterItems)->where($OrFilterItems);

      $includeRow = $request->query('includeRow');
      $includeRoom = $request->query('includeRoom');
      if ($includeRow && $includeRoom) {
        $niches = $niches->with('row.room'); // Get relationship of relationship (cascade)
      } else if ($includeRow) {
        $niches = $niches->with('row');
      }

      $includeUrns = $request->query('includeUrns');
      if ($includeUrns) {
        $niches = $niches->with('urns');
      }

      return new NicheCollection($niches->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource filter by dependency id.
     */
    public function getNichesFromRow(Request $request)
    {
      $rowId = $request->query('rowId');
      if (!$rowId) {
        return [];
      }

      $filter = new NicheFilter();
      [$AndFilterItems, $OrFilterItems] = $filter->transform($request); //[['column', 'operator', 'value']]

      $niches = Niche::where('row_id', '=', $rowId)->where($AndFilterItems)->where($OrFilterItems);

      $includeRow = $request->query('includeRow');
      $includeRoom = $request->query('includeRoom');
      if ($includeRow && $includeRoom) {
        $niches = $niches->with('row.room'); // Get relationship of relationship (cascade)
      } else if ($includeRow) {
        $niches = $niches->with('row');
      }

      $includeUrns = $request->query('includeUrns');
      if ($includeUrns) {
        $niches = $niches->with('urns');
      }

      return new NicheCollection($niches->orderBy('internal_code')->paginate(25)->appends($request->query()));
    }

    /**
     * Display a listing of the resource by dependency id.
     */
    public function getAllNichesFromRowNoPagination($rowId)
    {
      $niches = Niche::where('row_id', '=', $rowId)->orderBy('internal_code')->get();
      return new NicheCollection($niches);
    }

    /**
     * Display a listing of the resource without pagination.
     */
    public function getAllNichesByIdAndRow(Request $request)
    {
      $row_id = $request->rowId;
      $niche_ids = $request->nicheIds;

      $niches = Niche::where('row_id', '=', $row_id)->whereIn('id', $niche_ids)->orderBy('internal_code')->get();
      return new NicheCollection($niches);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNicheRequest $request)
    {
      $niche = new NicheResource(Niche::create($request->all()));

      if ($niche->storage_quantity > 0) {
        $urns = [];
        for ($i=0; $i < $niche->storage_quantity; $i++) {
          $urns[] = [
            'internal_code' => $niche->internal_code.'-'.sprintf("%02d", $i+1),
            'status' => 'AVAILABLE',
            'niche_id' => $niche->id,
            'description' => null
          ];
        }
        (new UrnController)->bulkStore($urns);
      }

      return $niche;
    }

    public function getNichesStatus() {
      $nichesStatus = [
        'available' => [],
        'reserved' => [],
        'expired' => [],
        'occupied' => [],
        'disabled' => []
      ];

      $niches = Niche::whereBetween('row_id', [1, 3])->whereBetween('id', [1, 165])->with('urns')->get();
      foreach ($niches as $niche) {
        $contAvailable = 0;
        $contReserved = 0;
        $contExpired = 0;
        $contOccupied = 0;
        $contDisabled = 0;

        foreach ($niche->urns as $urn) {
          if ($urn->status === 'AVAILABLE') {
            $contAvailable++;
          } else if ($urn->status === 'RESERVED') {
            $contReserved++;
          } else if ($urn->status === 'EXPIRED') {
            $contExpired++;
          } else if ($urn->status === 'OCCUPIED') {
            $contOccupied++;
          } else {
            $contDisabled++;
          }
        }

        if ($contAvailable > 0) {
          $nichesStatus['available'][] = $niche->id;
        } else if ($contReserved > 0) {
          $nichesStatus['reserved'][] = $niche->id;
        } else if ($contExpired > 0) {
          $nichesStatus['expired'][] = $niche->id;
        } else if ($contOccupied > 0) {
          $nichesStatus['occupied'][] = $niche->id;
        } else {
          $nichesStatus['disabled'][] = $niche->id;
        }
      }

      return $nichesStatus;
    }

    public function getNicheWithUrns($nicheId) {
      $niche = Niche::where('id', $nicheId)->with('urns', function($query) {
        $query->orderBy('internal_code', 'ASC');
      })->with('urns.relocations')->first();
      return new NicheResource($niche);
    }

    /**
     * Display the specified resource.
     */
    public function show(Niche $niche)
    {
      $includeRow = request()->query('includeRow');
      $includeRoom = request()->query('includeRoom');
      $includeBuilding = request()->query('includeBuilding');
      if ($includeRow && $includeRoom && $includeBuilding) {
        $niche = $niche->loadMissing('row.room.building'); // Get relationship of relationship (cascade)
      } else if ($includeRow && $includeRoom) {
        $niche = $niche->loadMissing('row.room'); // Get relationship of relationship (cascade)
      } else if ($includeRow) {
        $niche = $niche->loadMissing('row');
      }

      $includeUrns = request()->query('includeUrns');
      if($includeUrns) {
        $niche = $niche->loadMissing('urns');
      }

      return new NicheResource($niche);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNicheRequest $request, Niche $niche)
    {
      $niche->update($request->all());
      $this->updateChilds($niche);
      $this->addOrDeleteUrnsDependingStorage($niche);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Niche $niche)
    {
      $niche->delete();
    }

    public function updateStorageQuantity($nicheId) {
      $niche = Niche::with('urns')->find($nicheId);
      if ($niche && ($niche->storage_quantity != count($niche->urns))) {
        Niche::find($niche->id)->update([
          'internal_code' => $niche->internal_code,
          'storage_quantity' => count($niche->urns),
          'row_id' => $niche->row_id,
          'description' => $niche->description
        ]);
      }
    }

    public function bulkUpdate($niches)
    {
      foreach ($niches as $niche) {
        Niche::find($niche['id'])->update($niche);
        $this->updateChilds((object) $niche);
      }
    }

    public function updateChilds($niche) {
      $nicheWithUrns = new NicheResource(Niche::with(array('urns' => function($query) {
        $query->orderBy('internal_code', 'ASC');
      }))->find($niche->id));

      $urns = $nicheWithUrns->urns;

      // Check if niche (parent) internal code has changed to update all urns (child) internal code
      if (count($urns) > 0) {
        $urnsUpdate = [];

        foreach ($urns as $index => $urn) {
          $urnNicheInternalCode = substr($urn->internal_code, 0, strrpos($urn->internal_code, '-', 0)); // Get niche internal code from urn 01-01-01-01
          $urnSingleInternalCode = substr($urn->internal_code, strrpos($urn->internal_code, '-') + 1); // Get urn single part internal code 01

          if ($urnNicheInternalCode != $niche->internal_code) { // If are diferent we need to update to the new niche internal code
            $urnsUpdate[] = [
              'id' => $urn->id,
              'internal_code' => $niche->internal_code.'-'.$urnSingleInternalCode,
              'status' => $urn->status,
              'niche_id' => $urn->niche_id,
              'description' => $urn->description
            ];
          }
        }
        (new UrnController)->bulkUpdate($urnsUpdate);
      }
    }

    private function addOrDeleteUrnsDependingStorage($niche) {
      $nicheWithUrns = new NicheResource(Niche::with(array('urns' => function($query) {
        $query->orderBy('internal_code', 'ASC');
      }))->find($niche->id));

      $urns = $nicheWithUrns->urns;

      if ($niche->storage_quantity == count($urns)) {
        return;
      }

      // Delete leftover urns
      if ($niche->storage_quantity < count($urns)) {
        $urnsDelete = [];
        foreach ($urns as $key => $value) {
          if ($key >= $niche->storage_quantity) {
            $urnsDelete[] = $value->id;
          }
        }
        (new UrnController)->bulkDelete($urnsDelete);
        return;
      }

      // Add urns
      if ($niche->storage_quantity > count($urns)) {
        $urnsInsert = [];
        $lastUrnInserted = $urns[count($urns) - 1];
        $lastInternalCodeUsed = substr($lastUrnInserted->internal_code, strrpos($lastUrnInserted->internal_code, '-') + 1); // Get urn single part internal code 01

        for ($i = intval($lastInternalCodeUsed); $i < $niche->storage_quantity; $i++) {
          $urnsInsert[] = [
            'internal_code' => $niche->internal_code.'-'.sprintf("%02d", $i+1),
            'status' => 'AVAILABLE',
            'niche_id' => $niche->id,
            'description' => null
          ];
        }
        (new UrnController)->bulkStore($urnsInsert);
        return;
      }
    }
}
