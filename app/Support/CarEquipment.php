<?php

namespace App\Support;

/**
 * The equipment a car can come with: the single place where the options and their categories are defined.
 *
 * A car stores the keys of its options; the labels and the grouping always come from here.
 */
class CarEquipment
{
    /**
     * Every option, keyed by the value stored on a car, under the label of its category.
     *
     * @var array<string, array<string, string>>
     */
    protected const CATALOGUE = [
        'Safety' => [
            'abs' => 'Anti-lock brakes (ABS)',
            'automatic_emergency_braking' => 'Automatic emergency braking',
            'blind_spot_monitoring' => 'Blind-spot monitoring',
            'lane_keeping_assist' => 'Lane-keeping assist',
            'rear_cross_traffic_alert' => 'Rear cross-traffic alert',
            'backup_camera' => 'Backup camera',
            'adaptive_cruise_control' => 'Adaptive cruise control',
        ],
        'Comfort' => [
            'air_conditioning' => 'Air conditioning',
            'dual_zone_climate' => 'Dual-zone climate control',
            'heated_steering_wheel' => 'Heated steering wheel',
            'remote_start' => 'Remote start',
            'keyless_entry' => 'Keyless entry',
            'push_button_start' => 'Push-button start',
            'power_liftgate' => 'Power liftgate',
        ],
        'Technology' => [
            'apple_carplay' => 'Apple CarPlay',
            'android_auto' => 'Android Auto',
            'navigation' => 'Navigation system',
            'bluetooth' => 'Bluetooth',
            'wireless_charging' => 'Wireless phone charging',
            'head_up_display' => 'Head-up display',
            'digital_instrument_cluster' => 'Digital instrument cluster',
        ],
        'Audio' => [
            'am_fm_radio' => 'AM/FM radio',
            'satellite_radio' => 'Satellite radio',
            'premium_sound_system' => 'Premium sound system',
            'subwoofer' => 'Subwoofer',
            'usb_audio_input' => 'USB audio input',
            'steering_wheel_audio_controls' => 'Steering wheel audio controls',
            'rear_seat_entertainment' => 'Rear-seat entertainment',
        ],
        'Lighting' => [
            'led_headlights' => 'LED headlights',
            'automatic_headlights' => 'Automatic headlights',
            'automatic_high_beams' => 'Automatic high beams',
            'fog_lights' => 'Fog lights',
            'daytime_running_lights' => 'Daytime running lights',
            'led_taillights' => 'LED taillights',
            'ambient_interior_lighting' => 'Ambient interior lighting',
        ],
        'Seating' => [
            'heated_front_seats' => 'Heated front seats',
            'heated_rear_seats' => 'Heated rear seats',
            'ventilated_front_seats' => 'Ventilated front seats',
            'power_driver_seat' => 'Power driver seat',
            'driver_seat_memory' => 'Driver seat memory',
            'leather_seats' => 'Leather seats',
            'third_row_seating' => 'Third-row seating',
        ],
        'Exterior' => [
            'alloy_wheels' => 'Alloy wheels',
            'sunroof' => 'Sunroof',
            'panoramic_roof' => 'Panoramic roof',
            'roof_rails' => 'Roof rails',
            'heated_mirrors' => 'Heated mirrors',
            'power_folding_mirrors' => 'Power-folding mirrors',
            'rain_sensing_wipers' => 'Rain-sensing wipers',
        ],
        'Interior' => [
            'leather_steering_wheel' => 'Leather-wrapped steering wheel',
            'auto_dimming_mirror' => 'Auto-dimming rearview mirror',
            'split_folding_rear_seat' => 'Split-folding rear seat',
            'all_weather_floor_mats' => 'All-weather floor mats',
            'cargo_cover' => 'Cargo cover',
            'power_windows' => 'Power windows',
            'garage_door_opener' => 'Garage door opener',
        ],
        'Towing' => [
            'trailer_hitch' => 'Trailer hitch',
            'tow_package' => 'Tow package',
            'trailer_brake_controller' => 'Trailer brake controller',
            'trailer_sway_control' => 'Trailer sway control',
            'trailer_wiring_harness' => 'Trailer wiring harness',
            'tow_haul_mode' => 'Tow/haul mode',
            'tow_mirrors' => 'Tow mirrors',
        ],
        'Performance' => [
            'turbocharged_engine' => 'Turbocharged engine',
            'sport_suspension' => 'Sport suspension',
            'drive_mode_selector' => 'Drive mode selector',
            'paddle_shifters' => 'Paddle shifters',
            'limited_slip_differential' => 'Limited-slip differential',
            'performance_brakes' => 'Performance brakes',
            'sport_exhaust' => 'Sport exhaust',
        ],
    ];

    /**
     * Get every category with its options as value and label pairs, for the checkboxes of the car form.
     *
     * @return list<array{label: string, options: list<array{value: string, label: string}>}>
     */
    public static function catalogue(): array
    {
        $catalogue = [];

        foreach (self::CATALOGUE as $category => $options) {
            $catalogue[] = [
                'label' => $category,
                'options' => array_map(
                    fn (string $key, string $label) => ['value' => $key, 'label' => $label],
                    array_keys($options),
                    $options,
                ),
            ];
        }

        return $catalogue;
    }

    /**
     * Get the key of every option, whatever its category.
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_merge(...array_map(array_keys(...), array_values(self::CATALOGUE)));
    }

    /**
     * Get the labels of the selected options under their category, in catalogue order.
     * Categories without a selected option and keys that are not in the catalogue are left out.
     *
     * @param  list<string>  $selectedKeys
     * @return list<array{category: string, options: list<string>}>
     */
    public static function grouped(array $selectedKeys): array
    {
        $grouped = [];

        foreach (self::CATALOGUE as $category => $options) {
            $selected = array_values(array_intersect_key($options, array_flip($selectedKeys)));

            if ($selected !== []) {
                $grouped[] = ['category' => $category, 'options' => $selected];
            }
        }

        return $grouped;
    }
}
