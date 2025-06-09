<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderServiceType;
use App\Models\ProviderType;
use App\Models\Type;
use App\Models\Service;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    use Responses;


     public function searchProviders(Request $request)
    {
        try {
            $query = Provider::with([
                'providerTypes' => function ($query) {
                    $query->where('activate', 1)
                          ->where('status', 1)
                          ->with([
                              'type',
                              'services.service',
                              'images' => function ($query) {
                                  $query->limit(1);
                              }
                          ]);
                }
            ])
            ->whereHas('providerTypes', function ($query) {
                $query->where('activate', 1)->where('status', 1);
            })
            ->where('activate', 1);

            // Search filters
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name_of_manager', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('providerTypes', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                   ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                                   ->orWhere('address', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('providerTypes.type', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('name_en', 'LIKE', "%{$searchTerm}%")
                                   ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%");
                      })
                      ->orWhereHas('providerTypes.services.service', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('name_en', 'LIKE', "%{$searchTerm}%")
                                   ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%");
                      });
                });
            }

            // Type filter
            if ($request->has('type_id') && !empty($request->type_id)) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where('type_id', $request->type_id);
                });
            }

            // VIP filter
            if ($request->has('is_vip')) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where('is_vip', $request->is_vip);
                });
            }

            // Price range filter
            if ($request->has('min_price')) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where('price_per_hour', '>=', $request->min_price);
                });
            }

            if ($request->has('max_price')) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where('price_per_hour', '<=', $request->max_price);
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at'); // Default sort
            $sortOrder = $request->get('sort_order', 'desc'); // Default order

            switch ($sortBy) {
                case 'name':
                    $query->orderBy('name_of_manager', $sortOrder);
                    break;
                case 'price_low':
                    $query->join('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                          ->where('provider_types.activate', 1)
                          ->where('provider_types.status', 1)
                          ->orderBy('provider_types.price_per_hour', 'asc')
                          ->select('providers.*')
                          ->distinct();
                    break;
                case 'price_high':
                    $query->join('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                          ->where('provider_types.activate', 1)
                          ->where('provider_types.status', 1)
                          ->orderBy('provider_types.price_per_hour', 'desc')
                          ->select('providers.*')
                          ->distinct();
                    break;
                case 'vip':
                    $query->join('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                          ->where('provider_types.activate', 1)
                          ->where('provider_types.status', 1)
                          ->orderBy('provider_types.is_vip', 'desc')
                          ->select('providers.*')
                          ->distinct();
                    break;
                case 'created_at':
                default:
                    $query->orderBy('created_at', $sortOrder);
                    break;
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $providers = $query->paginate($perPage);

            // Transform data
            $providersData = $providers->getCollection()->map(function ($provider) {
                return $this->transformProviderData($provider, false);
            });

            return $this->success_response('Search results retrieved successfully', [
                'providers' => $providersData,
                'pagination' => [
                    'current_page' => $providers->currentPage(),
                    'last_page' => $providers->lastPage(),
                    'per_page' => $providers->perPage(),
                    'total' => $providers->total(),
                    'from' => $providers->firstItem(),
                    'to' => $providers->lastItem(),
                ],
                'search_params' => [
                    'search' => $request->search,
                    'type_id' => $request->type_id,
                    'is_vip' => $request->is_vip,
                    'min_price' => $request->min_price,
                    'max_price' => $request->max_price,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ]
            ]);

        } catch (\Exception $e) {
            return $this->error_response('Failed to search providers', $e->getMessage());
        }
    }

    /**
     * Get VIP providers only
     * GET /api/v1/providers/vip
     */
    public function getVipProviders(Request $request)
    {
        try {
            $query = Provider::with([
                'providerTypes' => function ($query) {
                    $query->where('activate', 1)
                          ->where('status', 1)
                          ->where('is_vip', 1) // Only VIP types
                          ->with([
                              'type',
                              'services.service',
                              'images' => function ($query) {
                                  $query->limit(1);
                              }
                          ]);
                }
            ])
            ->whereHas('providerTypes', function ($query) {
                $query->where('activate', 1)
                      ->where('status', 1)
                      ->where('is_vip', 1);
            })
            ->where('activate', 1);

            // Optional type filter for VIP providers
            if ($request->has('type_id')) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where('type_id', $request->type_id)
                      ->where('is_vip', 1);
                });
            }

            // Sorting options for VIP providers
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            switch ($sortBy) {
                case 'name':
                    $query->orderBy('name_of_manager', $sortOrder);
                    break;
                case 'price_low':
                    $query->join('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                          ->where('provider_types.activate', 1)
                          ->where('provider_types.status', 1)
                          ->where('provider_types.is_vip', 1)
                          ->orderBy('provider_types.price_per_hour', 'asc')
                          ->select('providers.*')
                          ->distinct();
                    break;
                case 'price_high':
                    $query->join('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                          ->where('provider_types.activate', 1)
                          ->where('provider_types.status', 1)
                          ->where('provider_types.is_vip', 1)
                          ->orderBy('provider_types.price_per_hour', 'desc')
                          ->select('providers.*')
                          ->distinct();
                    break;
                case 'created_at':
                default:
                    $query->orderBy('created_at', $sortOrder);
                    break;
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $vipProviders = $query->paginate($perPage);

            // Transform data
            $vipProvidersData = $vipProviders->getCollection()->map(function ($provider) {
                return $this->transformProviderData($provider, false);
            });

            return $this->success_response('VIP providers retrieved successfully', [
                'vip_providers' => $vipProvidersData,
                'pagination' => [
                    'current_page' => $vipProviders->currentPage(),
                    'last_page' => $vipProviders->lastPage(),
                    'per_page' => $vipProviders->perPage(),
                    'total' => $vipProviders->total(),
                    'from' => $vipProviders->firstItem(),
                    'to' => $vipProviders->lastItem(),
                ],
                'filters' => [
                    'type_id' => $request->type_id,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ]
            ]);

        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve VIP providers', $e->getMessage());
        }
    }

    
    public function getMapLocations()
    {
        try {
            // Get providers where 'activate' is 1 and have active provider types
            $providers = Provider::where('activate', 1)
                ->whereHas('providerTypes', function ($query) {
                    $query->where('activate', 1);
                })
                ->with([
                    'providerTypes' => function ($query) {
                        $query->where('activate', 1)
                            ->with([
                                'type',
                                'services.service',
                                'images'
                            ]);
                    }
                ])
                ->get();

            // Transform data
            $providersData = $providers->map(function ($provider) {
                return $this->transformProviderData($provider, false); // false = listing view
            });

            return $this->success_response('Providers retrieved successfully', [
                'providers' => $providersData,
                'total_providers' => $providersData->count()
            ]);

        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve providers', $e->getMessage());
        }
    }


    private function transformProviderData($provider, $includeFullDetails = false)
    {
        return [
            'id' => $provider->id,
            'name_of_manager' => $provider->name_of_manager,
            'phone' => $provider->country_code . $provider->phone,
            'email' => $provider->email,
            'photo_of_manager' => $provider->photo_of_manager ? 
                asset('assets/admin/uploads/' . $provider->photo_of_manager) : null,
            'balance' => $provider->balance,
            'types' => $provider->providerTypes->map(function ($providerType) use ($includeFullDetails) {
                return $this->transformProviderTypeData($providerType, $includeFullDetails);
            })
        ];
    }

    /**
     * Transform provider type data for API response
     * @param ProviderType $providerType
     * @param bool $includeFullDetails
     * @return array
     */
    private function transformProviderTypeData($providerType, $includeFullDetails = false)
    {
        $data = [
            'id' => $providerType->id,
            'name' => $providerType->name,
            'description' => $providerType->description,
            'price_per_hour' => $providerType->price_per_hour,
            'address' => $providerType->address,
            'lat' => $providerType->lat,
            'lng' => $providerType->lng,
            'is_vip' => $providerType->is_vip == 1,
            'type' => [
                'id' => $providerType->type->id,
                'name_en' => $providerType->type->name_en,
                'name_ar' => $providerType->type->name_ar,
            ],
            'services' => $providerType->services->map(function ($service) {
                return [
                    'id' => $service->service->id,
                    'name_en' => $service->service->name_en,
                    'name_ar' => $service->service->name_ar,
                ];
            }),
        ];

        if ($includeFullDetails) {
            // Full details include all images, galleries, availability, etc.
            $data['images'] = $providerType->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'photo_url' => asset('assets/admin/uploads/' . $image->photo),
                ];
            });

            $data['galleries'] = $providerType->galleries->map(function ($gallery) {
                return [
                    'id' => $gallery->id,
                    'photo_url' => asset('assets/admin/uploads/' . $gallery->photo),
                ];
            });

            $data['availability'] = $this->formatAvailability($providerType->availabilities);
            
            $data['upcoming_unavailability'] = $providerType->unavailabilities->map(function ($unavailability) {
                // Ensure date is a Carbon instance
                $date = $unavailability->unavailable_date instanceof \Carbon\Carbon 
                    ? $unavailability->unavailable_date 
                    : \Carbon\Carbon::parse($unavailability->unavailable_date);
                
                return [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('l'),
                    'type' => $unavailability->unavailable_type,
                    'start_time' => $unavailability->start_time ? 
                        (is_string($unavailability->start_time) ? $unavailability->start_time : $unavailability->start_time->format('H:i')) : null,
                    'end_time' => $unavailability->end_time ? 
                        (is_string($unavailability->end_time) ? $unavailability->end_time : $unavailability->end_time->format('H:i')) : null,
                ];
            });
        } else {
            // Listing view - only first image
            $data['image'] = $providerType->images->first() ? 
                asset('assets/admin/uploads/' . $providerType->images->first()->photo) : null;
        }

        return $data;
    }

    /**
     * Get all providers under a specific type
     * GET /api/v1/providers/type/{typeId}
     */
    public function getProvidersByType($typeId)
    {
        try {
            // Check if type exists
            $type = Type::find($typeId);
            if (!$type) {
                return $this->error_response('Type not found', null);
            }

            // Get providers with their types, services, and images
            $providers = Provider::whereHas('providerTypes', function ($query) use ($typeId) {
                $query->where('type_id', $typeId)
                      ->where('activate', 1)
                      ->where('status', 1);
            })
            ->with([
                'providerTypes' => function ($query) use ($typeId) {
                    $query->where('type_id', $typeId)
                          ->where('activate', 1)
                          ->where('status', 1)
                          ->with([
                              'type',
                              'services.service',
                              'images' => function ($query) {
                                  $query->limit(1); // Get only first image for listing
                              }
                          ]);
                }
            ])
            ->where('activate', 1)
            ->get();

            // Transform data using our reusable function
            $providersData = $providers->map(function ($provider) {
                return $this->transformProviderData($provider, false); // false = listing view
            });

            return $this->success_response('Providers retrieved successfully', [
                'type' => [
                    'id' => $type->id,
                    'name_en' => $type->name_en,
                    'name_ar' => $type->name_ar,
                ],
                'providers' => $providersData,
                'total_providers' => $providersData->count()
            ]);

        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve providers', $e->getMessage());
        }
    }

    /**
     * Get provider details by ID
     * GET /api/v1/providers/{providerId}
     */
    public function getProviderDetails($providerId)
    {
        try {
            // Get provider with all related data
            $provider = Provider::with([
                'providerTypes' => function ($query) {
                    $query->where('activate', 1)
                          ->where('status', 1)
                          ->with([
                              'type',
                              'services.service',
                              'images',
                              'galleries',
                              'availabilities' => function ($query) {
                                  $query->orderBy('day_of_week');
                              },
                              'unavailabilities' => function ($query) {
                                  $query->where('unavailable_date', '>=', now()->toDateString())
                                        ->orderBy('unavailable_date');
                              }
                          ]);
                }
            ])
            ->where('activate', 1)
            ->find($providerId);

            if (!$provider) {
                return $this->error_response('Provider not found', null);
            }

            // Transform provider data using our reusable function
            $providerData = $this->transformProviderData($provider, true); // true = full details

            return $this->success_response('Provider details retrieved successfully', $providerData);

        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve provider details', $e->getMessage());
        }
    }

    /**
     * Helper method to format availability data
     */
    private function formatAvailability($availabilities)
    {
        $daysOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        $formattedAvailability = [];
        
        foreach ($daysOrder as $day) {
            $dayAvailability = $availabilities->where('day_of_week', $day)->first();
            
            $formattedAvailability[] = [
                'day' => $day,
                'available' => $dayAvailability ? true : false,
                'start_time' => $dayAvailability ? 
                    (is_string($dayAvailability->start_time) ? $dayAvailability->start_time : $dayAvailability->start_time->format('H:i')) : null,
                'end_time' => $dayAvailability ? 
                    (is_string($dayAvailability->end_time) ? $dayAvailability->end_time : $dayAvailability->end_time->format('H:i')) : null,
            ];
        }
        
        return $formattedAvailability;
    }


   

}