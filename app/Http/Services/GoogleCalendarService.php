<?php

namespace App\Http\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    /**
     * @throws ConnectionException
     */
    public function getValidToken()
    {
        $data = Cache::get('google_tokens_' . auth()?->id());

        if (empty($data)) {
            return null;
        }

        if (isset($data['expires_in']) && Carbon::parse($data['expires_in'])->isPast()) {
            $refreshToken = $data['refresh_token'];
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');

            $refreshResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ]);

            if ($refreshResponse->failed()) {
                return null;
            }

            $newTokens = $refreshResponse->json();
            $data['access_token'] = $newTokens['access_token'];

            if (isset($newTokens['expires_in'])) {
                $expireTime = Carbon::now()->addSeconds($newTokens['expires_in']);
                $data['expires_in'] = $expireTime->toDateTimeString();
            }

            Cache::put('google_tokens_' . auth()?->id(), $data);
        }

        return $data['access_token'];
    }

    /**
     * @throws ConnectionException
     */
    public function createEvent(array $eventData): array
    {
        $accessToken = $this->getValidToken();

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }

        $eventPayload = [
            'summary'     => $eventData['summary'] ?? 'No Title',
            'location'    => $eventData['location'] ?? '',
            'description' => $eventData['description'] ?? '',
            'start'       => [
                'dateTime' => Carbon::parse($eventData['start_time'])->toIso8601String(),
                'timeZone' => 'Asia/Yerevan',
            ],
            'end'         => [
                'dateTime' => Carbon::parse($eventData['end_time'])->toIso8601String(),
                'timeZone' => 'Asia/Yerevan',
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json'
        ])->post('https://www.googleapis.com/calendar/v3/calendars/primary/events', $eventPayload);

        if ($response->failed()) {
            return ['success' => false, 'error' => 'Failed to create event'];
        }

        return ['success' => true, 'event' => $response->json()];
    }

    /**
     * @throws ConnectionException
     */
    public function deleteEvent(string $eventId): array
    {
        $accessToken = $this->getValidToken();

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->delete("https://www.googleapis.com/calendar/v3/calendars/primary/events/{$eventId}");

        if ($response->failed()) {
            Log::error('Google Calendar event deletion failed', [
                'response' => $response->json(),
                'status' => $response->status(),
                'eventId' => $eventId,
            ]);
            return ['success' => false, 'error' => 'Failed to delete event'];
        }

        return ['success' => true, 'message' => 'Event deleted successfully'];
    }

    /**
     * @throws ConnectionException
     */
    public function getCalendarEvents(): array
    {
        $accessToken = $this->getValidToken();

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://www.googleapis.com/calendar/v3/calendars/primary/events');

        if ($response->failed()) {
            return ['success' => false, 'error' => 'Failed to fetch events'];
        }

        return ['success' => true, 'events' => $response->json()];
    }
}
