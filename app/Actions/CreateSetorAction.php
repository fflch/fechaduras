<?php

namespace App\Actions;

use App\Models\Setor;

class CreateSetorAction
{
    /**
     * Create a new class instance.
     */

    public static function execute($setores, $fechadura): void
    {
        if ( empty($setores) ){
            $fechadura->setores()->detach();
        }
        else {
            $setoresId = [];
            foreach ($setores as $codset) {
                $setor = Setor::firstOrCreate(
                    ['codset' => $codset],
                );
                array_push($setoresId, $setor->id);
            }

            $fechadura->setores()->sync($setoresId);
        }
    }

}
