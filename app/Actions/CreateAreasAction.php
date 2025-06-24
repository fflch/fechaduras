<?php

namespace App\Actions;

use App\Models\Area;

class CreateAreasAction
{
    /**
     * Create a new class instance.
     */
    public static function execute($areas, $fechadura): void
    {
        $setoresId = [];
            foreach($areas as $codare){
                $setor_pos = Area::firstOrCreate(
                    ['codare' => $codare]
                );
                array_push($setoresId, $setor_pos->id);
            }
            $fechadura->areas()->sync($setoresId);
    }
}
