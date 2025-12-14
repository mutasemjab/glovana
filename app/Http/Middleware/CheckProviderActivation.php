<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProviderActivation
{
    /**
     * Route endpoints that banned providers (activate=2) ARE allowed to access
     */
    protected $bannedProviderAllowedEndpoints = [
        'withdrawal/request',
        'update_profile',
        'delete_account',
        'notifications',
    ];

    /**
     * Route endpoints that waiting approval providers (activate=3) ARE allowed to access
     */
    protected $waitingApprovalAllowedEndpoints = [
        'withdrawal/request',
        'update_profile',
        'delete_account',
        'notifications',
        'complete-profile',
        'types',  // This allows /types/{providerTypeId}
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('provider-api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 'error'
            ], 401);
        }

        // Get language from header (default to 'en')
        $lang = $request->header('lang', 'en');

        // Check for waiting approval (activate = 3)
        if ($user->activate == 3) {
            // Allow all GET requests (read-only access)
            if ($request->isMethod('get')) {
                return $next($request);
            }
            
            // For POST/PUT/DELETE requests, check if it's in allowed list for waiting approval
            if ($this->isEndpointAllowedForWaitingApproval($request->path())) {
                return $next($request);
            }
            
            // Block other requests
            $message = $lang === 'ar' 
                ? 'حسابك قيد المراجعة. يمكنك تحديث ملفك الشخصي ونوع الخدمة أثناء انتظار الموافقة.' 
                : 'Your account is under review. You can update your profile and service type while waiting for approval.';
            
            return response()->json([
                'message' => $message,
                'status' => 'pending_approval',
                'activate' => 3,
                'allowed_actions' => [
                    'view_data' => true,
                    'update_profile' => true,
                    'complete_profile' => true,
                    'update_service_type' => true,
                    'withdrawal' => true,
                    'delete_account' => true,
                    'view_notifications' => true,
                ]
            ], 403);
        }

        // Check if provider is banned (activate = 2)
        if ($user->activate == 2) {
            $banInfo = $this->getBanInfo($user, $lang);
            
            // Allow all GET requests (read-only access)
            if ($request->isMethod('get')) {
                // Add ban info to request for awareness
                $request->attributes->set('is_banned', true);
                $request->attributes->set('ban_info', $banInfo);
                return $next($request);
            }
            
            // For POST/PUT/DELETE requests, check if it's in allowed list for banned providers
            if ($this->isEndpointAllowedForBanned($request->path())) {
                return $next($request);
            }
            
            // Block all other non-GET requests
            $message = $lang === 'ar' 
                ? 'تم حظر حسابك. يمكنك فقط عرض المعلومات، سحب رصيدك، وتحديث ملفك الشخصي.' 
                : 'Your account has been banned. You can only view information, withdraw your balance, and update your profile.';
            
            return response()->json([
                'message' => $message,
                'status' => 'banned',
                'activate' => 2,
                'ban_info' => $banInfo,
                'allowed_actions' => [
                    'view_data' => true,
                    'withdrawal' => true,
                    'update_profile' => true,
                    'delete_account' => true,
                    'view_notifications' => true,
                    'update_service_type' => false,  // NOT allowed for banned
                ]
            ], 403);
        }

        // Provider is active (activate = 1)
        return $next($request);
    }

    /**
     * Check if the endpoint is allowed for banned providers (activate = 2)
     */
    protected function isEndpointAllowedForBanned($currentPath)
    {
        foreach ($this->bannedProviderAllowedEndpoints as $endpoint) {
            if (str_ends_with($currentPath, $endpoint)) {
                return true;
            }
            
            if (str_ends_with($currentPath, '/' . $endpoint)) {
                return true;
            }
            
            // Check if path contains the endpoint (for dynamic segments)
            if (str_contains($currentPath, $endpoint)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the endpoint is allowed for waiting approval providers (activate = 3)
     */
    protected function isEndpointAllowedForWaitingApproval($currentPath)
    {
        foreach ($this->waitingApprovalAllowedEndpoints as $endpoint) {
            if (str_ends_with($currentPath, $endpoint)) {
                return true;
            }
            
            if (str_ends_with($currentPath, '/' . $endpoint)) {
                return true;
            }
            
            // Check if path contains the endpoint (for dynamic segments like /types/{id})
            if (str_contains($currentPath, '/' . $endpoint . '/')) {
                return true;
            }
            
            // Special handling for routes with parameters
            if ($endpoint === 'types' && preg_match('#/types/\d+$#', $currentPath)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get ban information for the provider
     */
    protected function getBanInfo($provider, $lang = 'en')
    {
        $activeBan = $provider->activeBan;
        
        if (!$activeBan) {
            return [
                'is_banned' => true,
                'reason' => $lang === 'ar' ? 'غير محدد' : 'Not specified',
            ];
        }

        $banInfo = [
            'id' => $activeBan->id,
            'is_permanent' => $activeBan->is_permanent,
            'reason' => $activeBan->ban_reason,
            'reason_text' => $activeBan->getReasonText($lang),
            'description' => $activeBan->ban_description,
            'banned_at' => $activeBan->banned_at->toDateTimeString(),
            'banned_at_human' => $activeBan->banned_at->diffForHumans(),
        ];

        if (!$activeBan->is_permanent && $activeBan->ban_until) {
            $banInfo['ban_until'] = $activeBan->ban_until->toDateTimeString();
            $banInfo['ban_until_human'] = $activeBan->ban_until->diffForHumans();
            $banInfo['remaining_time'] = $activeBan->getRemainingTime($lang);
            $banInfo['is_expired'] = $activeBan->isExpired();
        }

        return $banInfo;
    }
}