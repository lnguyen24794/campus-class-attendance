<?php

namespace App\Http\Resources\Api\V1\Periods;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin Period
 */
class PeriodResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'starts_at' => Str::of((string) $this->starts_at)->substr(0, 5)->toString(),
            'ends_at' => Str::of((string) $this->ends_at)->substr(0, 5)->toString(),
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}
