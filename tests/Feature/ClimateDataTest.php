<?php

namespace Tests\Feature;

use App\Models\ClimateData;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClimateDataTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for authenticated requests
        $this->adminUser = User::factory()->create([
            'role' => UserRole::RECRUITER,
            'is_active' => true,
        ]);
        
        $this->adminToken = $this->adminUser->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function climate_data_can_be_created()
    {
        $climateData = [
            'recorded_at' => '2023-01-01 12:00:00',
            'temperature' => 25.5,
            'source' => 'weather_station_1',
        ];

        $climate = ClimateData::create($climateData);

        $this->assertDatabaseHas('climate_data', [
            'temperature' => 25.5,
            'source' => 'weather_station_1',
        ]);

        $this->assertEquals(25.5, $climate->temperature);
        $this->assertEquals('weather_station_1', $climate->source);
    }

    /** @test */
    public function climate_data_can_be_retrieved()
    {
        $climate = ClimateData::factory()->create([
            'temperature' => 30.0,
            'source' => 'test_station',
        ]);

        $found = ClimateData::find($climate->id);

        $this->assertNotNull($found);
        $this->assertEquals(30.0, $found->temperature);
        $this->assertEquals('test_station', $found->source);
    }

    /** @test */
    public function climate_data_can_be_listed()
    {
        ClimateData::factory()->count(5)->create();

        $climateData = ClimateData::all();

        $this->assertCount(5, $climateData);
    }

    /** @test */
    public function climate_data_can_be_filtered_by_temperature_range()
    {
        ClimateData::factory()->create(['temperature' => 10.0]);
        ClimateData::factory()->create(['temperature' => 25.0]);
        ClimateData::factory()->create(['temperature' => 35.0]);

        $hotWeather = ClimateData::minTemperature(30.0)->get();
        $this->assertCount(1, $hotWeather);
        $this->assertEquals(35.0, $hotWeather->first()->temperature);

        $coldWeather = ClimateData::maxTemperature(15.0)->get();
        $this->assertCount(1, $coldWeather);
        $this->assertEquals(10.0, $coldWeather->first()->temperature);
    }

    /** @test */
    public function climate_data_can_be_filtered_by_date_period()
    {
        ClimateData::factory()->create([
            'recorded_at' => '2023-01-01 12:00:00',
            'temperature' => 20.0,
        ]);
        
        ClimateData::factory()->create([
            'recorded_at' => '2023-06-01 12:00:00',
            'temperature' => 30.0,
        ]);
        
        ClimateData::factory()->create([
            'recorded_at' => '2023-12-01 12:00:00',
            'temperature' => 15.0,
        ]);

        $summerData = ClimateData::forPeriod('2023-05-01', '2023-07-31')->get();
        
        $this->assertCount(1, $summerData);
        $this->assertEquals(30.0, $summerData->first()->temperature);
    }

    /** @test */
    public function climate_data_can_be_queried_and_analyzed()
    {
        // Create some test data
        ClimateData::factory()->create(['temperature' => 20.0, 'recorded_at' => '2023-01-01']);
        ClimateData::factory()->create(['temperature' => 25.0, 'recorded_at' => '2023-01-02']);
        ClimateData::factory()->create(['temperature' => 30.0, 'recorded_at' => '2023-01-03']);

        // Test basic querying functionality
        $allData = ClimateData::all();
        $this->assertCount(3, $allData);

        // Test temperature statistics
        $avgTemp = ClimateData::avg('temperature');
        $this->assertEquals(25.0, $avgTemp);

        $maxTemp = ClimateData::max('temperature');
        $this->assertEquals(30.0, $maxTemp);

        $minTemp = ClimateData::min('temperature');
        $this->assertEquals(20.0, $minTemp);
    }

    /** @test */
    public function climate_data_can_be_bulk_deleted()
    {
        ClimateData::factory()->count(5)->create();
        $this->assertDatabaseCount('climate_data', 5);

        // Test bulk delete functionality
        ClimateData::truncate();
        $this->assertDatabaseCount('climate_data', 0);
    }

    /** @test */
    public function climate_data_can_be_filtered_and_deleted_by_date_range()
    {
        ClimateData::factory()->create([
            'recorded_at' => '2023-01-01 12:00:00',
        ]);
        
        ClimateData::factory()->create([
            'recorded_at' => '2023-06-01 12:00:00',
        ]);
        
        ClimateData::factory()->create([
            'recorded_at' => '2023-12-01 12:00:00',
        ]);

        $this->assertDatabaseCount('climate_data', 3);

        // Delete records within a specific date range
        ClimateData::forPeriod('2023-05-01', '2023-07-31')->delete();
        
        // Should delete only the June record
        $this->assertDatabaseCount('climate_data', 2);
    }

    /** @test */
    public function climate_data_bulk_delete_with_temperature_range_works()
    {
        ClimateData::factory()->create(['temperature' => 10.0]);
        ClimateData::factory()->create(['temperature' => 25.0]);
        ClimateData::factory()->create(['temperature' => 35.0]);

        $this->assertDatabaseCount('climate_data', 3);

        // Delete records with temperature >= 30.0 using model methods
        ClimateData::where('temperature', '>=', 30.0)->delete();
        
        // Should delete only records with temperature >= 30
        $this->assertDatabaseCount('climate_data', 2);
        
        // Verify the remaining records have temperature < 30
        $remainingRecords = ClimateData::all();
        foreach ($remainingRecords as $record) {
            $this->assertTrue($record->temperature < 30.0);
        }
    }

    /** @test */
    public function climate_data_model_casts_work_correctly()
    {
        $climate = ClimateData::factory()->create([
            'recorded_at' => '2023-01-01 12:00:00',
            'temperature' => 25.50,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $climate->recorded_at);
        $this->assertEquals(25.50, $climate->temperature);
        // Temperature should be a float/double type
        $this->assertTrue(is_numeric($climate->temperature));
    }
}
