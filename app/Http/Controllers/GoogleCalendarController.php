<?php

namespace App\Http\Controllers;

use App\Http\Services\GoogleCalendarService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class GoogleCalendarController extends Controller
{
    public function __construct(protected GoogleCalendarService $googleService){}
    public function createEvent(Request $request): JsonResponse
    {
        $eventData = $request->all();
        $result = $this->googleService->createEvent($eventData);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
    public function deleteEvent(string $eventId): JsonResponse
    {
        $result = $this->googleService->deleteEvent($eventId);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * @throws ConnectionException
     */
    public function getCalendarEvents(): JsonResponse
    {
        $result = $this->googleService->getCalendarEvents();

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
