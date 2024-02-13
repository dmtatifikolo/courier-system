<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'userType' => $this->user_type,
            // 'userId' => $this->user_id,
            'event' => $this->event,
            'auditableType' => $this->auditable_type,
            'oldValues' => $this->old_values,
            'newValues' => $this->new_values,
            'ipAddress' => $this->ip_address,
            'userAgent' => $this->user_agent,
            'createdAt' => date("Y-m-d H:i:s", strtotime($this->created_at)),
            'updatedAt' => date("Y-m-d H:i:s", strtotime($this->updated_at)),
            'user' => isset($this->user) ? $this->user->name : null,
        ];

        //     "id": 1,
        //     "user_type": "App\\Models\\User",
        //     "user_id": 1,
        //     "event": "updated",
        //     "auditable_type": "App\\Models\\User",
        //     "auditable_id": 3,
        //     "old_values": {
        //         "mobile_number": "0755666333"
        //     },
        //     "new_values": {
        //         "mobile_number": "0755666334"
        //     },
        //     "url": "http://127.0.0.1:8000/api/user/3",
        //     "ip_address": "127.0.0.1",
        //     "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:102.0) Gecko/20100101 Firefox/102.0",
        //     "tags": null,
        //     "created_at": "2023-03-27T10:05:49.000000Z",
        //     "updated_at": "2023-03-27T10:05:49.000000Z",
        //     "user": {
        //         "id": 1,
        //         "name": "Administrator",
        //         "email": "admin@test.go.tz",
        //         "username": "admin",
        //         "email_verified_at": null,
        //         "two_factor_secret": null,
        //         "two_factor_recovery_codes": null,
        //         "title": "Sales Officer",
        //         "mobile_number": "0755222333",
        //         "created_at": "2023-03-19T09:38:27.000000Z",
        //         "updated_at": "2023-03-19T09:38:27.000000Z"
        //      }
    }
}
