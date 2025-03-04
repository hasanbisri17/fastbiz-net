<?php

namespace App\Http\Controllers;

use App\Services\RouterOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouterOSController extends Controller
{
    protected $routerOS;

    public function __construct(RouterOSService $routerOS)
    {
        $this->routerOS = $routerOS;
    }

    /**
     * Get system resources
     */
    public function systemResources(): JsonResponse
    {
        try {
            $resources = $this->routerOS->getSystemResources();
            return response()->json(['data' => $resources]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all interfaces
     */
    public function interfaces(): JsonResponse
    {
        try {
            $interfaces = $this->routerOS->getInterfaces();
            return response()->json(['data' => $interfaces]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get active hotspot users
     */
    public function hotspotActiveUsers(): JsonResponse
    {
        try {
            $users = $this->routerOS->getHotspotActiveUsers();
            return response()->json(['data' => $users]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add new hotspot user
     */
    public function addHotspotUser(Request $request): JsonResponse
    {
        try {
            $result = $this->routerOS->addHotspotUser(
                $request->input('name'),
                $request->input('password'),
                $request->input('profile', 'default')
            );
            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
