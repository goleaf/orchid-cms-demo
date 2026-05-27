<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\SaveMarketingMessageTemplateAction;
use App\Http\Requests\Marketing\MessageTemplateRequest;
use App\Models\MarketingMessageTemplate;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class MessageTemplateEditScreen extends Screen
{
    public ?MarketingMessageTemplate $messageTemplate = null;

    public function query(MarketingMessageTemplate $messageTemplate): iterable
    {
        if (! $messageTemplate->exists) {
            $messageTemplate->forceFill([
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        $this->messageTemplate = $messageTemplate;

        return [
            'messageTemplate' => $messageTemplate,
            'template' => $messageTemplate,
        ];
    }

    public function name(): ?string
    {
        return $this->messageTemplate?->exists
            ? tkey('crm.message_templates.edit_title')
            : tkey('crm.message_templates.create_title');
    }

    public function description(): ?string
    {
        return tkey('crm.message_templates.description');
    }

    public function permission(): iterable
    {
        return ['platform.marketing.templates'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.marketing.templates'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(tkey('common.actions.delete'))
                ->icon('bs.trash3')
                ->method('delete')
                ->canSee($this->messageTemplate?->exists ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('template.name')
                    ->title(tkey('crm.message_templates.fields.name'))
                    ->maxlength(190)
                    ->required(),

                Select::make('template.channel')
                    ->title(tkey('crm.message_templates.fields.channel'))
                    ->empty(tkey('crm.communication.channels.any'), '')
                    ->options(MarketingMessageTemplate::channelOptions()),

                Input::make('template.subject')
                    ->title(tkey('crm.message_templates.fields.subject'))
                    ->maxlength(190),

                TextArea::make('template.body')
                    ->title(tkey('crm.message_templates.fields.body'))
                    ->rows(6)
                    ->required(),

                Switcher::make('template.is_active')
                    ->title(tkey('crm.message_templates.fields.is_active'))
                    ->sendTrueOrFalse(),

                Input::make('template.sort_order')
                    ->type('number')
                    ->title(tkey('crm.message_templates.fields.sort_order'))
                    ->min(0)
                    ->required(),
            ]),
        ];
    }

    public function save(
        MessageTemplateRequest $request,
        MarketingMessageTemplate $messageTemplate,
        SaveMarketingMessageTemplateAction $saveTemplate,
    ): RedirectResponse {
        $saveTemplate->handle($messageTemplate, $request->templateData());

        Toast::info(tkey('crm.message_templates.messages.saved'));

        return redirect()->route('platform.marketing.templates');
    }

    public function delete(MarketingMessageTemplate $messageTemplate): RedirectResponse
    {
        abort_unless(request()->user()?->hasAccess('platform.marketing.templates'), 403);

        $messageTemplate->delete();

        Toast::info(tkey('crm.message_templates.messages.deleted'));

        return redirect()->route('platform.marketing.templates');
    }
}
