<?php

namespace Database\Factories;

use App\Models\ClimateData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClimateData>
 */
class ClimateDataFactory extends Factory
{
    protected $model = ClimateData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recorded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'temperature' => $this->faker->randomFloat(2, -10, 40), // Temperature between -10°C and 40°C
            'source' => $this->faker->randomElement([
                'weather_station_1',
                'weather_station_2',
                'satellite_data',
                'manual_reading',
                'automated_sensor'
            ]),
            'imported_at' => now(),
        ];
    }

    /**
     * Create climate data with hot weather
     */
    public function hot(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => $this->faker->randomFloat(2, 30, 45),
        ]);
    }

    /**
     * Create climate data with cold weather
     */
    public function cold(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => $this->faker->randomFloat(2, -15, 5),
        ]);
    }

    /**
     * Create climate data for a specific date
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => $date,
        ]);
    }

    /**
     * Create climate data from a specific source
     */
    public function fromSource(string $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => $source,
        ]);
    }
}
