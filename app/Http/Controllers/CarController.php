<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use App\Models\Car;
use App\Services\CarService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    protected CarService $carService;

    /**
     * @param CarService $carService
     */
    public function __construct(CarService $carService)
    {
        $this->carService = $carService;
    }


    /**
     * @return Factory|Application|View
     */

    public function index(): View|Application|Factory
    {
        $cars = $this->carService->getVisibleCars(auth()->check())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $cars->getCollection()->transform(function ($car) {
            return $this->carService->prepareCarForDisplay($car);
        });

        return view('cars.index', compact('cars'));
    }

    public function create(): View|Application|Factory
    {
        return view('cars.create', [
            'types' => $this->carService->getCarTypes(),
            'fuelTypes' => $this->carService->getFuelTypes(),
            'transmissions' => $this->carService->getTransmissions()
        ]);
    }

    /**
     * @param StoreCarRequest $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(StoreCarRequest $request): RedirectResponse
    {
        $car = $this->carService->createCar($request->validated(), $request->file('main_image'), $request->file('gallery_images'));

        return redirect()->route('cars.show', $car->id)
            ->with('success', __('messages.car_created'));
    }

    /**
     * @param Car $car
     * @return Factory|Application|View
     */
    public function show(Car $car): View|Application|Factory
    {
        $this->carService->checkCarVisibility($car);
        $car = $this->carService->prepareCarForDisplay($car);

        return view('cars.show', compact('car'));
    }

    /**
     * @param Car $car
     * @return Factory|Application|View
     */
    public function edit(Car $car): View|Application|Factory
    {
        $car = $this->carService->prepareCarForEdit($car);

        return view('cars.edit', [
            'car' => $car,
            'types' => $this->carService->getCarTypes(),
            'fuelTypes' => $this->carService->getFuelTypes(),
            'transmissions' => $this->carService->getTransmissions()
        ]);
    }

    /**
     * @param UpdateCarRequest $request
     * @param Car $car
     * @return RedirectResponse
     */
    public function update(UpdateCarRequest $request, Car $car): RedirectResponse
    {
        $this->carService->updateCar(
            $car,
            $request->validated(),
            $request->file('main_image'),
            $request->file('gallery_images'),
            $request->new_main_image,
            $request->delete_old_main_image,
            $request->delete_main_image,
            $request->delete_gallery_images
        );

        return redirect()->route('cars.show', $car->id)
            ->with('success', __('messages.car_updated'));
    }


    /**
     * @param Car $car
     * @return RedirectResponse
     */
    public function destroy(Car $car): RedirectResponse
    {
        $this->carService->deleteCar($car);

        return redirect()->route('cars.index')
            ->with('success', __('messages.car_deleted'));
    }

    /**
     * Switch hide the car
     * @param Car $car
     * @return RedirectResponse
     */
    public function toggleVisibility(Car $car): RedirectResponse
    {
        $this->carService->toggleCarVisibility($car);

        return back()->with([
            'success' => 'Car visibility updated',
            'scroll_position' => request()->header('Referer') . '#car-' . $car->id
        ]);
    }
}
