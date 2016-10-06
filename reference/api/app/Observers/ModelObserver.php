<?php

namespace App\Observers;

class ModelObserver
{
    /**
     * Listen to the User deleting event.
     *
     * @param  User  $user
     * @return void
     */
    public function deleting (Model $model)
    {
        echo $model::className . "\n";
        $className = $model::class;
        $relationships = $className::$RELATIONSHIPS;
        foreach (array_keys($relationships['has_many']) as $relationship) {
                $model->{$relationship}()->delete();
            }
        });
        foreach ($array_keys($relationships['belongs_to_and_has_many']) as $relationship) {
            $model->{$relationship}()->detach();
        }
        // return false;
    }
}