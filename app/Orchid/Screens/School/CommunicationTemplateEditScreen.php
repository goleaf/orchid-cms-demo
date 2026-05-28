<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateCommunicationTemplateAction;
use App\Http\Requests\Communication\CommunicationTemplateRequest;
use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CommunicationTemplateEditScreen extends Screen
{
    public ?CommunicationTemplate $communicationTemplate = null;

    public function query(CommunicationTemplate $communicationTemplate): iterable
    {
        if (! $communicationTemplate->exists) {
            $communicationTemplate->forceFill([
                'type' => CommunicationTemplate::TYPE_GENERAL,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        $this->communicationTemplate = $communicationTemplate;

        return [
            'communicationTemplate' => $communicationTemplate,
            'template' => $communicationTemplate,
            'template.name_translations' => $communicationTemplate->name_translations ?? [],
            'template.subject_translations' => $communicationTemplate->subject_translations ?? [],
            'template.body_translations' => $communicationTemplate->body_translations ?? [],
        ];
    }

    public function name(): ?string
    {
        return $this->communicationTemplate?->exists
            ? tkey('communication.templates.edit_title')
            : tkey('communication.templates.create_title');
    }

    public function description(): ?string
    {
        return tkey('communication.templates.description');
    }

    public function permission(): iterable
    {
        return ['communications.templates.manage'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.communications.templates'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('template.id')->type('hidden'),

                Input::make('template.code')
                    ->title(tkey('communication.channels.fields.code'))
                    ->maxlength(120),

                Select::make('template.type')
                    ->title(tkey('communication.templates.fields.type'))
                    ->options(CommunicationTemplate::typeOptions())
                    ->required(),

                Select::make('template.notification_channel_id')
                    ->title(tkey('communication.templates.fields.channel'))
                    ->empty(tkey('communication.channels.any'), '')
                    ->options(NotificationChannel::options()),

                Switcher::make('template.is_active')
                    ->title(tkey('communication.common.fields.is_active'))
                    ->sendTrueOrFalse(),

                Input::make('template.sort_order')
                    ->type('number')
                    ->title(tkey('communication.common.fields.sort_order'))
                    ->min(0),
            ]),

            TranslatableFields::input('template.name', 'communication.channels.fields.name', [
                'maxlength' => 255,
                'required' => true,
            ]),

            TranslatableFields::input('template.subject', 'communication.templates.fields.subject', [
                'maxlength' => 255,
            ]),

            TranslatableFields::textarea('template.body', 'communication.templates.fields.body', [
                'rows' => 6,
                'maxlength' => 5000,
                'required' => true,
            ]),
        ];
    }

    public function save(
        CommunicationTemplateRequest $request,
        CommunicationTemplate $communicationTemplate,
        CreateOrUpdateCommunicationTemplateAction $saveTemplate,
    ): RedirectResponse {
        $saveTemplate->handle($communicationTemplate, $request->templateData());

        Toast::info(tkey('communication.common.messages.saved'));

        return redirect()->route('platform.communications.templates');
    }
}
