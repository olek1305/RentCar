<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use App\Models\Car;
use App\Models\Order;
use App\Services\CacheService;
use App\Services\CarService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CarController extends Controller
{
    /**
     * Initialize controller with car service and cache service dependency injection
     */
    public function __construct(
        protected CarService $carService,
        protected CacheService $cacheService
    ) {
        //
    }

    /**
     * Display a paginated listing of cars with caching
     * Shows all cars for admin users, only visible cars for guests
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(): View|Application|Factory
    {
        $currentPage = (int) request()->get('page', 1);
        $perPage = 12;

        // Get cache key for current page
        $cacheKey = $this->cacheService->getCarCacheKey($currentPage, $perPage);

        // Track cache keys for easier invalidation
        $this->cacheService->trackCacheKey($cacheKey);

        // Cache the query results properly, 1 hour
        $carsData = Cache::remember($cacheKey, 3600, function () use ($perPage, $currentPage) {
            $query = $this->carService->getVisibleCars(auth()->check())
                ->orderBy('created_at', 'desc');

            $total = $query->count();
            $cars = $query->skip(($currentPage - 1) * $perPage)
                ->take($perPage)
                ->get();

            return [
                'cars' => $cars,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
            ];
        });

        // Create paginator manually to maintain correct pagination state
        $cars = new LengthAwarePaginator(
            $carsData['cars'],
            $carsData['total'],
            $carsData['per_page'],
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        // Transform cars for display
        $cars->getCollection()->transform(fn ($car) => $this->carService->prepareCarForDisplay($car)
        );

        return view('cars.index', compact('cars'));
    }

    /**
     * Show the form for creating a new car
     */
    public function create(): View|Application|Factory
    {
        return view('cars.create', [
            'types' => $this->carService->getCarTypes(),
            'fuelTypes' => $this->carService->getFuelTypes(),
            'transmissions' => $this->carService->getTransmissions(),
        ]);
    }

    /**
     * Store a newly created car in the database
     */
    public function store(StoreCarRequest $request): RedirectResponse
    {
        $car = $this->carService->createCar(
            $request->validated(),
            $request->file('main_image'),
            $request->file('gallery_images')
        );
        $this->cacheService->clearCarsCache();

        return redirect()->route('cars.show', $car->id)
            ->with('success', __('messages.car_created'));
    }

    /**
     * Display the specified car with order information
     */
    public function show(Car $car, Order $order): View|Application|Factory
    {
        $this->carService->checkCarVisibility($car);
        $car = $this->carService->prepareCarForDisplay($car);

        return view('cars.show', compact('car', 'order'));
    }

    /**
     * Show the form for editing the specified car
     */
    public function edit(Car $car): View|Application|Factory
    {
        $car = $this->carService->prepareCarForEdit($car);

        return view('cars.edit', [
            'car' => $car,
            'types' => $this->carService->getCarTypes(),
            'fuelTypes' => $this->carService->getFuelTypes(),
            'transmissions' => $this->carService->getTransmissions(),
        ]);
    }

    /**
     * Update the specified car in the database
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
        $this->cacheService->clearCarsCache();

        return redirect()->route('cars.show', $car->id)
            ->with('success', __('messages.car_updated'));
    }

    /**
     * Remove the specified car from the database
     */
    public function destroy(Car $car): RedirectResponse
    {
        $this->carService->deleteCar($car);
        $this->cacheService->clearCarsCache();

        return redirect()->route('cars.index')
            ->with('success', __('messages.car_deleted'));
    }

    /**
     * Switch hide the car
     */
    public function toggleVisibility(Car $car): RedirectResponse
    {
        $this->carService->toggleCarVisibility($car);
        $this->cacheService->clearCarsCache();

        return back()->with([
            'success' => 'Car visibility updated',
            'scroll_position' => request()->header('Referer').'#car-'.$car->id,
        ]);
    }
}
