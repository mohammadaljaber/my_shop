<?php


namespace App\Http\Controllers\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository{

    public function __construct(protected Model $model){}

    public function index(){
        return $this->model::paginate();
    }

    public function show(Model $model)
    {
        return $model;
    }

    public function store($data): Model
    {
        return $this->model::create($data);
    }

    public function update(Model $model, $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    public function getRelation(Model $model ,?string $relation)
    {
        return $model->load($relation);
    }

}
