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

                        ->with([
                            'type',
                             'discounts' => function ($query) {
                                $query->active()->current()->with('services');
                            },
                            'services.service',
                            'providerServices.service', // Add provider services with pricing
                            'images' => function ($query) {
                                $query->limit(1);
                            }
                        ]);
                }
            ])
                ->whereHas('providerTypes', function ($query) {
                    $query->where('activate', 1);
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
                        })
                        ->orWhereHas('providerTypes.providerServices.service', function ($subQuery) use ($searchTerm) {
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

            // Price range filter - updated to handle both hourly and service pricing
            if ($request->has('min_price')) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where(function ($priceQuery) use ($request) {
                        // For hourly types, check price_per_hour
                        $priceQuery->where(function ($hourlyQuery) use ($request) {
                            $hourlyQuery->whereHas('type', function ($typeQuery) {
                                $typeQuery->where('booking_type', 'hourly');
                            })->where('price_per_hour', '>=', $request->min_price);
                        })
                            // For service types, check provider_services prices
                            ->orWhere(function ($serviceQuery) use ($request) {
                                $serviceQuery->whereHas('type', function ($typeQuery) {
                                    $typeQuery->where('booking_type', 'service');
                                })->whereHas('providerServices', function ($providerServiceQuery) use ($request) {
                                    $providerServiceQuery->where('price', '>=', $request->min_price)
                                        ->where('is_active', 1);
                                });
                            });
                    });
                });
            }

            if ($request->has('max_price')) {
                $query->whereHas('providerTypes', function ($q) use ($request) {
                    $q->where(function ($priceQuery) use ($request) {
                        // For hourly types, check price_per_hour
                        $priceQuery->where(function ($hourlyQuery) use ($request) {
                            $hourlyQuery->whereHas('type', function ($typeQuery) {
                                $typeQuery->where('booking_type', 'hourly');
                            })->where('price_per_hour', '<=', $request->max_price);
                        })
                            // For service types, check provider_services prices
                            ->orWhere(function ($serviceQuery) use ($request) {
                                $serviceQuery->whereHas('type', function ($typeQuery) {
                                    $typeQuery->where('booking_type', 'service');
                                })->whereHas('providerServices', function ($providerServiceQuery) use ($request) {
                                    $providerServiceQuery->where('price', '<=', $request->max_price)
                                        ->where('is_active', 1);
                                });
                            });
                    });
                });
            }

            // Service-specific price filter
            if ($request->has('service_id') && !empty($request->service_id)) {
                $query->whereHas('providerTypes.providerServices', function ($q) use ($request) {
                    $q->where('service_id', $request->service_id)
                        ->where('is_active', 1);

                    // Add price filters for specific service
                    if ($request->has('service_min_price')) {
                        $q->where('price', '>=', $request->service_min_price);
                    }
                    if ($request->has('service_max_price')) {
                        $q->where('price', '<=', $request->service_max_price);
                    }
                });
            }

            // Sorting - updated to handle service pricing
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            switch ($sortBy) {
                case 'name':
                    $query->orderBy('name_of_manager', $sortOrder);
                    break;
                case 'price_low':
                    $query->leftJoin('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                        ->leftJoin('types', 'provider_types.type_id', '=', 'types.id')
                        ->leftJoin('provider_services', 'provider_types.id', '=', 'provider_services.provider_type_id')
                        ->where('provider_types.activate', 1)
                        ->where('provider_types.status', 1)
                        ->selectRaw('providers.*, 
                                     CASE 
                                         WHEN types.booking_type = "hourly" THEN provider_types.price_per_hour
                                         ELSE COALESCE(MIN(provider_services.price), 0)
                                     END as sort_price')
                        ->groupBy('providers.id')
                        ->orderBy('sort_price', 'asc');
                    break;
                case 'price_high':
                    $query->leftJoin('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                        ->leftJoin('types', 'provider_types.type_id', '=', 'types.id')
                        ->leftJoin('provider_services', 'provider_types.id', '=', 'provider_services.provider_type_id')
                        ->where('provider_types.activate', 1)
                        ->where('provider_types.status', 1)
                        ->selectRaw('providers.*, 
                                     CASE 
                                         WHEN types.booking_type = "hourly" THEN provider_types.price_per_hour
                                         ELSE COALESCE(MAX(provider_services.price), 0)
                                     END as sort_price')
                        ->groupBy('providers.id')
                        ->orderBy('sort_price', 'desc');
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
                    'service_id' => $request->service_id,
                    'service_min_price' => $request->service_min_price,
                    'service_max_price' => $request->service_max_price,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ]
            ]);
        } catch (\Exception $e) {
            return $this->error_response('Failed to search providers', $e->getMessage());
        }
    }

    public function getVipProviders(Request $request)
    {
        try {
            $query = Provider::with([
                'providerTypes' => function ($query) {
                    $query->where('activate', 1)

                        ->where('is_vip', 1)
                        ->with([
                            'type',
                             'discounts' => function ($query) {
                                $query->active()->current()->with('services');
                            },
                            'services.service',
                            'providerServices.service',
                            'images'
                        ]);
                }
            ])
                ->whereHas('providerTypes', function ($query) {
                    $query->where('activate', 1)

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
                    $query->leftJoin('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                        ->leftJoin('types', 'provider_types.type_id', '=', 'types.id')
                        ->leftJoin('provider_services', 'provider_types.id', '=', 'provider_services.provider_type_id')
                        ->where('provider_types.activate', 1)
                        ->where('provider_types.status', 1)
                        ->where('provider_types.is_vip', 1)
                        ->selectRaw('providers.*, 
                                     CASE 
                                         WHEN types.booking_type = "hourly" THEN provider_types.price_per_hour
                                         ELSE COALESCE(MIN(provider_services.price), 0)
                                     END as sort_price')
                        ->groupBy('providers.id')
                        ->orderBy('sort_price', 'asc');
                    break;
                case 'price_high':
                    $query->leftJoin('provider_types', 'providers.id', '=', 'provider_types.provider_id')
                        ->leftJoin('types', 'provider_types.type_id', '=', 'types.id')
                        ->leftJoin('provider_services', 'provider_types.id', '=', 'provider_services.provider_type_id')
                        ->where('provider_types.activate', 1)
                        ->where('provider_types.status', 1)
                        ->where('provider_types.is_vip', 1)
                        ->selectRaw('providers.*, 
                                     CASE 
                                         WHEN types.booking_type = "hourly" THEN provider_types.price_per_hour
                                         ELSE COALESCE(MAX(provider_services.price), 0)
                                     END as sort_price')
                        ->groupBy('providers.id')
                        ->orderBy('sort_price', 'desc');
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
            $providers = Provider::where('activate', 1)
                ->whereHas('providerTypes', function ($query) {
                    $query->where('activate', 1);
                })
                ->with([
                    'providerTypes' => function ($query) {
                        $query->where('activate', 1)
                            ->with([
                                'type',
                                 'discounts' => function ($query) {
                                    $query->active()->current()->with('services');
                                },
                                'services.service',
                                'providerServices.service',
                                'images'
                            ]);
                    }
                ])
                ->get();

            $providersData = $providers->map(function ($provider) {
                return $this->transformProviderData($provider, false);
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

    private function transformProviderTypeData($providerType, $includeFullDetails = false)
    {
        $data = [
            'id' => $providerType->id,
            'name' => $providerType->name,
            'description' => $providerType->description,
            'address' => $providerType->address,
            'lat' => $providerType->lat,
            'lng' => $providerType->lng,
            'status' => $providerType->status,
            'is_vip' => $providerType->is_vip == 1,
            'is_favourite' => $providerType->is_favourite,
            'type' => [
                'id' => $providerType->type->id,
                'name_en' => $providerType->type->name_en,
                'name_ar' => $providerType->type->name_ar,
                'booking_type' => $providerType->type->booking_type,
                'have_delivery' => $providerType->type->have_delivery,
                'minimum_order' => $providerType->type->minimum_order,
            ],
            'services' => $providerType->services->map(function ($service) {
                return [
                    'id' => $service->service->id,
                    'name_en' => $service->service->name_en,
                    'name_ar' => $service->service->name_ar,
                ];
            }),
            'ratings' => $providerType->ratings->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'review' => $rating->review,
                    'rating' => $rating->rating,
                    'user' => $rating->user,
                ];
            }),
        ];

        // Add pricing information based on booking type
        if ($providerType->type->booking_type === 'hourly') {
            $data['price_per_hour'] = $providerType->price_per_hour;
            $data['pricing_type'] = 'hourly';
        } else {
            $data['pricing_type'] = 'service';
            $data['provider_services'] = $providerType->providerServices
                ->where('is_active', 1)
                ->map(function ($providerService) {
                    return [
                        'id' => $providerService->id,
                        'service' => [
                            'id' => $providerService->service->id,
                            'name_en' => $providerService->service->name_en,
                            'name_ar' => $providerService->service->name_ar,
                        ],
                        'price' => $providerService->price,
                        'is_active' => $providerService->is_active,
                    ];
                });
        }

        if ($includeFullDetails) {
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
            $data['image'] = $providerType->images->first() ?
                asset('assets/admin/uploads/' . $providerType->images->first()->photo) : null;
        }

        return $data;
    }

    public function getProvidersByType($typeId)
    {
        try {
            $type = Type::find($typeId);
            if (!$type) {
                return $this->error_response('Type not found', null);
            }

            $providers = Provider::whereHas('providerTypes', function ($query) use ($typeId) {
                $query->where('type_id', $typeId)
                    ->where('activate', 1);
            })
                ->with([
                    'providerTypes' => function ($query) use ($typeId) {
                        $query->where('type_id', $typeId)
                            ->where('activate', 1)

                            ->with([
                                'type',
                                 'discounts' => function ($query) {
                                $query->active()->current()->with('services');
                            },
                                'services.service',
                                'providerServices.service',
                                'images',
                            ]);
                    }
                ])
                ->where('activate', 1)
                ->get();

            $providersData = $providers->map(function ($provider) {
                return $this->transformProviderData($provider, false);
            });

            return $this->success_response('Providers retrieved successfully', [
                'type' => [
                    'id' => $type->id,
                    'name_en' => $type->name_en,
                    'name_ar' => $type->name_ar,
                    'booking_type' => $type->booking_type,
                    'have_delivery' => $type->have_delivery,
                    'minimum_order' => $type->minimum_order,
                ],
                'providers' => $providersData,
                'total_providers' => $providersData->count()
            ]);
        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve providers', $e->getMessage());
        }
    }

     public function getProviderDetails($providerId)
    {
        try {
            // Load provider with all needed relations
            $provider = Provider::with([
                'providerTypes' => function ($query) {
                    $query->where('activate', 1)
                        ->with([
                            'type',
                            'discounts' => function ($query) {
                                $query->active()->current()->with('services');
                            },
                            'services.service',
                            'providerServices.service',
                            'images',
                            'galleries',
                            'availabilities' => function ($query) {
                                $query->orderBy('day_of_week');
                            },
                            'unavailabilities' => function ($query) {
                                $query->where('unavailable_date', '>=', now()->toDateString())
                                    ->orderBy('unavailable_date');
                            },
                            'ratings.user'
                        ]);
                }
            ])
                ->where('activate', 1)
                ->find($providerId);

            // If provider not found
            if (!$provider) {
                return $this->error_response('Provider not found', null);
            }

            // Transform provider data using your transformer method
            $providerData = $this->transformProviderData($provider, true);

            /** ✅ Provider overall average rating (based on ProviderType accessors) */
            $providerAvg = $provider->providerTypes->avg('avg_rating');
            $providerData['avg_rating'] = round($providerAvg ?? 0, 1);

            /** ✅ Find similar providers (same type_id as first providerType) */
            $firstTypeId = optional($provider->providerTypes->first())->type_id;

            $similarProviders = collect();
            if ($firstTypeId) {
                $similarProviders = Provider::whereHas('providerTypes', function ($query) use ($firstTypeId, $providerId) {
                    $query->where('type_id', $firstTypeId)
                        ->where('activate', 1);
                })
                    ->where('activate', 1)
                    ->where('id', '!=', $providerId)
                    ->with([
                        'providerTypes' => function ($query) {
                            $query->where('activate', 1)
                                ->with([
                                    'type',
                                    'images',
                                    'services.service',
                                    'providerServices.service'
                                ]);
                        }
                    ])
                    ->take(5)
                    ->get()
                    ->map(function ($similar) {
                        return $this->transformProviderData($similar, false);
                    });
            }

            /** ✅ Add similar providers to response */
            $providerData['similar_providers'] = $similarProviders;

            return $this->success_response('Provider details retrieved successfully', $providerData);
        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve provider details', $e->getMessage());
        }
    }


    /**
     * Get services with prices for a specific provider type
     * GET /api/v1/providers/{providerId}/types/{typeId}/services
     */
    public function getProviderTypeServices($providerId, $typeId)
    {
        try {
            $providerType = ProviderType::where('provider_id', $providerId)
                ->where('type_id', $typeId)
                ->where('activate', 1)

                ->with([
                    'type',
                    'providerServices' => function ($query) {
                        $query->where('is_active', 1)->with('service');
                    }
                ])
                ->first();

            if (!$providerType) {
                return $this->error_response('Provider type not found', null);
            }

            if ($providerType->type->booking_type !== 'service') {
                return $this->error_response('This provider type does not offer services', null);
            }

            $servicesData = $providerType->providerServices->map(function ($providerService) {
                return [
                    'id' => $providerService->id,
                    'service' => [
                        'id' => $providerService->service->id,
                        'name_en' => $providerService->service->name_en,
                        'name_ar' => $providerService->service->name_ar,
                    ],
                    'price' => $providerService->price,
                    'is_active' => $providerService->is_active,
                ];
            });

            return $this->success_response('Provider services retrieved successfully', [
                'provider_type' => [
                    'id' => $providerType->id,
                    'name' => $providerType->name,
                    'type' => [
                        'id' => $providerType->type->id,
                        'name_en' => $providerType->type->name_en,
                        'name_ar' => $providerType->type->name_ar,
                        'booking_type' => $providerType->type->booking_type,
                    ]
                ],
                'services' => $servicesData,
                'total_services' => $servicesData->count()
            ]);
        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve provider services', $e->getMessage());
        }
    }

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
