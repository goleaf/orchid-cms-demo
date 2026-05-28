<?php

namespace Database\Factories;

use App\Models\CommunicationAttachment;
use App\Models\CommunicationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunicationAttachment>
 */
class CommunicationAttachmentFactory extends Factory
{
    protected $model = CommunicationAttachment::class;

    public function definition(): array
    {
        return [
            'message_id' => CommunicationMessage::factory(),
            'disk' => 'local',
            'path' => 'communications/'.$this->faker->uuid().'.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(10_000, 500_000),
            'metadata' => null,
        ];
    }
}
