<?php

namespace App\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $user = $request->user();
        $user->load('driver');

        return $user;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|between:2010,2025',
            'make' => 'required',
            'model' => 'required',
            'color' => 'required',
            'license_plate' => 'required',
            'name' =>'required'
        ]);


        $user = $request->user();

        $user->update($request->only('name'));

        $user->driver()->updateOrCreate($request->only(['year','make','model','color','license_plate']));
        $user->load('driver');

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
