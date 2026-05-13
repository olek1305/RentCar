<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterCarsRequest;
use App\Models\Car;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class CarAdminController extends Controller
{
    /**
     * Display a listing of all cars for admin management.
     */
    public function index(FilterCarsRequest $request): Factory|Application|View
    {
        $query = Car::query()->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('model', 'like', '%'.$search.'%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('hidden')) {
            if ($request->input('hidden') === 'visible') {
                $query->where('hidden', false);
            } elseif ($request->input('hidden') === 'hidden') {
                $query->where('hidden', true);
            }
        }

        $cars = $query->paginate(10)->appends($request->query());

        return view('admin.cars.index', [
            'cars' => $cars,
            'types' => Car::TYPES,
            'filters' => [
                'search' => $request->input('search'),
                'type' => $request->input('type'),
                'hidden' => $request->input('hidden', 'all'),
            ],
        ]);
    }
}
