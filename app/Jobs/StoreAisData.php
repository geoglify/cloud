<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CargoType;
use App\Models\ShipHistoricalPosition;
use App\Models\ShipRealtimePosition;
use App\Models\Ship;
use Illuminate\Support\Facades\Log;

class StoreAisData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $aisData;

    /**
     * Create a new job instance.
     *
     * @param array $aisData
     */
    public function __construct(array $aisData)
    {
        $this->aisData = $aisData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Skip if the data is not an array
        if (!is_array($this->aisData)) {
            Log::warning("Skipping AIS data: Not an array", $this->aisData);
            return;
        }

        // Skip if aisData does not contain 'mmsi' key
        if (!isset($this->aisData['mmsi'])) {
            Log::warning("Skipping AIS data: Missing 'mmsi' key", $this->aisData);
            return;
        }
        
        Log::info("Storing AIS data", $this->aisData);

        // Update or create the ship and get the ship instance
        $ship = $this->updateOrCreateShip($this->aisData);

        // Use the ship ID to update/create realtime and historical positions
        $this->aisData['id'] = $ship->id; // Add the ship ID to the data array
        $this->updateOrCreateShipRealtimePosition($this->aisData);
        $this->createHistoricalPosition($this->aisData);
    }

    /**
     * Update or create the ship.
     * 
     * @param array $shipData
     * @return Ship
     */
    protected function updateOrCreateShip(array $shipData): Ship
    {
        // Get cargo type
        $cargoType = $this->getCargoType($shipData);

        // Update or create the ship
        return Ship::updateOrCreate(
            ['mmsi' => $shipData['mmsi']],
            $this->filterData([
                'name' => $shipData['name'] ?? null,
                'dim_a' => $shipData['dim_a'] ?? null,
                'dim_b' => $shipData['dim_b'] ?? null,
                'dim_c' => $shipData['dim_c'] ?? null,
                'dim_d' => $shipData['dim_d'] ?? null,
                'imo' => $shipData['imo'] ?? null,
                'callsign' => $shipData['callsign'] ?? null,
                'draught' => $shipData['draught'] ?? null,
                'cargo_type_id' => $cargoType?->id,
            ])
        );
    }

    /**
     * Update or create the realtime position for the ship.
     * 
     * @param array $shipData
     * @return void
     */
    protected function updateOrCreateShipRealtimePosition(array $shipData): void
    {
        // Update or create the realtime position
        ShipRealtimePosition::updateOrCreate(
            ['ship_id' => $shipData['id']], // Use the ship ID
            $this->filterData([
                'cog' => $shipData['cog'] ?? null,
                'sog' => $shipData['sog'] ?? null,
                'hdg' => $shipData['hdg'] ?? null,
                'last_updated' => $shipData['last_updated'] ?? null,
                'eta' => $shipData['eta'] ?? null,
                'destination' => $shipData['destination'] ?? null,
                'latitude' => $shipData['latitude'] ?? null,
                'longitude' => $shipData['longitude'] ?? null,
            ])
        );
    }

    /**
     * Create a historical position for the ship.
     * 
     * @param array $shipData
     * @return void
     */
    protected function createHistoricalPosition(array $shipData): void
    {
        ShipHistoricalPosition::create($this->filterData([
            'ship_id' => $shipData['id'], // Use the ship ID
            'mmsi' => $shipData['mmsi'],
            'cog' => $shipData['cog'] ?? null,
            'sog' => $shipData['sog'] ?? null,
            'hdg' => $shipData['hdg'] ?? null,
            'last_updated' => $shipData['last_updated'] ?? null,
            'eta' => $shipData['eta'] ?? null,
            'destination' => $shipData['destination'] ?? null,
            'latitude' => $shipData['latitude'] ?? null,
            'longitude' => $shipData['longitude'] ?? null,
        ]));
    }

    /**
     * Get the cargo type from the ship data.
     * 
     * @param array $shipData
     * @return CargoType|null
     */
    protected function getCargoType(array $shipData): ?CargoType
    {
        return isset($shipData['cargo'])
            ? CargoType::where('code', (int) $shipData['cargo'])->first()
            : null;
    }

    /**
     * Filter out empty values from the data array.
     * 
     * @param array $data
     * @return array
     */
    protected function filterData(array $data): array
    {
        return array_filter($data, fn($value) => $value !== '' && $value !== null);
    }
}
