<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/17/2022
 * Time: 10:17 PM
 */

namespace Tests\Feature\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ModelTestingHelper
{
    public function test_insert_data()
    {
        $model = $this->model();
        $data = $model::factory()->make()->toArray();
        if ($model instanceof User) {
            $data['password'] = '123456';
        }
        $model::create($data);
        $this->assertDatabaseHas($model->getTable(), $data);
    }

    abstract protected function model(): Model;
}