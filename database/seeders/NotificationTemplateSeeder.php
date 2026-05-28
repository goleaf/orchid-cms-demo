<?php

namespace Database\Seeders;

use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (NotificationChannel::query()->whereIn('code', ['internal', 'email', 'sms_placeholder'])->count() < 3) {
            $this->call(NotificationChannelSeeder::class);
        }

        $channels = NotificationChannel::query()
            ->whereIn('code', ['internal', 'email', 'sms_placeholder'])
            ->get(['id', 'code'])
            ->keyBy('code');

        foreach ($this->records() as $record) {
            $factory = NotificationTemplate::factory();
            $state = $record['state'] ?? null;

            if (is_string($state) && method_exists($factory, $state)) {
                $factory = $factory->{$state}();
            }

            $template = $factory->system()->active()->make([
                'code' => $record['code'],
                'channel_id' => $channels[$record['channel_code']]?->id ?? null,
                'name_translations' => $record['name_translations'],
                'description_translations' => $record['description_translations'],
                'template_group' => $record['template_group'],
            ]);

            $attributes = $template->only($template->getFillable());
            unset($attributes['code']);

            /** @var NotificationTemplate $template */
            $template = NotificationTemplate::query()->updateOrCreate(
                ['code' => $record['code']],
                $attributes,
            );

            $this->upsertPublishedVersion($template, $record);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function upsertPublishedVersion(NotificationTemplate $template, array $record): void
    {
        $version = NotificationTemplateVersion::factory()->published()->make([
            'template_id' => $template->id,
            'version' => 1,
            'subject_translations' => $record['subject_translations'],
            'body_translations' => $record['body_translations'],
            'variables_schema' => $this->schema($record['variables']),
        ]);

        $attributes = $version->only($version->getFillable());
        unset($attributes['template_id'], $attributes['version']);

        NotificationTemplateVersion::query()->updateOrCreate(
            [
                'template_id' => $template->id,
                'version' => 1,
            ],
            $attributes,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function records(): array
    {
        return [
            [
                'code' => 'student_welcome',
                'state' => 'studentWelcome',
                'channel_code' => 'email',
                'template_group' => 'student',
                'name_translations' => $this->translations('Приветствие ученика', 'Student welcome', 'Mokinio pasveikinimas', 'Powitanie ucznia'),
                'description_translations' => $this->translations('Первое сообщение новому ученику после записи.', 'First message for a new student after enrollment.', 'Pirmoji zinute naujam mokiniui po registracijos.', 'Pierwsza wiadomosc do nowego ucznia po zapisie.'),
                'subject_translations' => $this->translations('Добро пожаловать в автошколу', 'Welcome to the driving school', 'Sveiki atvyke i vairavimo mokykla', 'Witamy w szkole jazdy'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. Мы рады видеть вас в {{ school_name }}.', 'Hello {{ student_name }}. We are glad to welcome you to {{ school_name }}.', 'Sveiki, {{ student_name }}. Dziaugiames jus priimdami i {{ school_name }}.', 'Dzien dobry, {{ student_name }}. Cieszymy sie, ze jestes w {{ school_name }}.'),
                'variables' => ['student_name' => 'string', 'school_name' => 'string'],
            ],
            [
                'code' => 'lead_follow_up',
                'state' => 'leadFollowUp',
                'channel_code' => 'email',
                'template_group' => 'lead',
                'name_translations' => $this->translations('Повторный контакт с лидом', 'Lead follow-up', 'Pakartotinis kontaktas su uzklausa', 'Ponowny kontakt z leadem'),
                'description_translations' => $this->translations('Сообщение для повторного контакта после заявки.', 'Message for a follow-up after an inquiry.', 'Zinute pakartotiniam kontaktui po uzklausos.', 'Wiadomosc po zapytaniu od leada.'),
                'subject_translations' => $this->translations('Ваша заявка на обучение', 'Your driving course inquiry', 'Jusu uzklausa del vairavimo kursu', 'Twoje zapytanie o kurs jazdy'),
                'body_translations' => $this->translations('Здравствуйте, {{ lead_name }}. Подскажите, когда удобно обсудить обучение?', 'Hello {{ lead_name }}. When is a good time to discuss training?', 'Sveiki, {{ lead_name }}. Kada patogu aptarti mokymus?', 'Dzien dobry, {{ lead_name }}. Kiedy mozemy omowic kurs?'),
                'variables' => ['lead_name' => 'string', 'manager_name' => 'string'],
            ],
            [
                'code' => 'lesson_reminder',
                'state' => 'lessonReminder',
                'channel_code' => 'email',
                'template_group' => 'lesson',
                'name_translations' => $this->translations('Напоминание о занятии', 'Lesson reminder', 'Pamokos priminimas', 'Przypomnienie o lekcji'),
                'description_translations' => $this->translations('Напоминание ученику о ближайшем занятии.', 'Reminder for a student about an upcoming lesson.', 'Priminimas mokiniui apie artejancia pamoka.', 'Przypomnienie uczniowi o nadchodzacej lekcji.'),
                'subject_translations' => $this->translations('Напоминание о занятии', 'Lesson reminder', 'Pamokos priminimas', 'Przypomnienie o lekcji'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. Ваше занятие назначено на {{ lesson_date }} в {{ lesson_time }}.', 'Hello {{ student_name }}. Your lesson is scheduled for {{ lesson_date }} at {{ lesson_time }}.', 'Sveiki, {{ student_name }}. Jusu pamoka suplanuota {{ lesson_date }} {{ lesson_time }}.', 'Dzien dobry, {{ student_name }}. Lekcja jest zaplanowana na {{ lesson_date }} o {{ lesson_time }}.'),
                'variables' => ['student_name' => 'string', 'lesson_date' => 'date', 'lesson_time' => 'string', 'instructor_name' => 'string'],
            ],
            [
                'code' => 'driving_lesson_reminder',
                'state' => 'lessonReminder',
                'channel_code' => 'sms_placeholder',
                'template_group' => 'lesson',
                'name_translations' => $this->translations('SMS напоминание о вождении', 'Driving lesson reminder', 'Vairavimo pamokos priminimas', 'Przypomnienie o jezdzie'),
                'description_translations' => $this->translations('Короткая заготовка для будущего SMS о практическом занятии.', 'Short placeholder for a future practical lesson SMS.', 'Trumpas busimos praktines pamokos SMS paruosinys.', 'Krotki szablon przyszlego SMS o jezdzie.'),
                'subject_translations' => $this->translations('Занятие по вождению', 'Driving lesson', 'Vairavimo pamoka', 'Jazda praktyczna'),
                'body_translations' => $this->translations('{{ student_name }}, занятие по вождению: {{ lesson_date }} {{ lesson_time }}.', '{{ student_name }}, driving lesson: {{ lesson_date }} {{ lesson_time }}.', '{{ student_name }}, vairavimo pamoka: {{ lesson_date }} {{ lesson_time }}.', '{{ student_name }}, jazda: {{ lesson_date }} {{ lesson_time }}.'),
                'variables' => ['student_name' => 'string', 'lesson_date' => 'date', 'lesson_time' => 'string'],
            ],
            [
                'code' => 'payment_due',
                'state' => 'paymentReminder',
                'channel_code' => 'email',
                'template_group' => 'finance',
                'name_translations' => $this->translations('Напоминание об оплате', 'Payment due', 'Mokejimo priminimas', 'Przypomnienie o platnosci'),
                'description_translations' => $this->translations('Напоминание ученику о предстоящей оплате.', 'Reminder for a student about an upcoming payment.', 'Priminimas mokiniui apie artejanti mokejima.', 'Przypomnienie uczniowi o zblizajacej sie platnosci.'),
                'subject_translations' => $this->translations('Напоминание об оплате', 'Payment reminder', 'Mokejimo priminimas', 'Przypomnienie o platnosci'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. К оплате {{ payment_amount }} до {{ due_date }}.', 'Hello {{ student_name }}. {{ payment_amount }} is due by {{ due_date }}.', 'Sveiki, {{ student_name }}. Moketina suma {{ payment_amount }} iki {{ due_date }}.', 'Dzien dobry, {{ student_name }}. Kwota {{ payment_amount }} jest wymagana do {{ due_date }}.'),
                'variables' => ['student_name' => 'string', 'payment_amount' => 'money', 'due_date' => 'date'],
            ],
            [
                'code' => 'document_missing',
                'state' => null,
                'channel_code' => 'email',
                'template_group' => 'documents',
                'name_translations' => $this->translations('Не хватает документа', 'Missing document', 'Truksta dokumento', 'Brakujacy dokument'),
                'description_translations' => $this->translations('Сообщение о недостающем документе ученика.', 'Message about a missing student document.', 'Zinute apie trukstama mokinio dokumenta.', 'Wiadomosc o brakujacym dokumencie ucznia.'),
                'subject_translations' => $this->translations('Нужен документ для обучения', 'Document needed for training', 'Mokymui reikalingas dokumentas', 'Dokument potrzebny do kursu'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. Пожалуйста, загрузите документ: {{ document_name }}.', 'Hello {{ student_name }}. Please upload the document: {{ document_name }}.', 'Sveiki, {{ student_name }}. Prasome ikelti dokumenta: {{ document_name }}.', 'Dzien dobry, {{ student_name }}. Przeslij dokument: {{ document_name }}.'),
                'variables' => ['student_name' => 'string', 'document_name' => 'string'],
            ],
            [
                'code' => 'document_rejected',
                'state' => 'documentRejected',
                'channel_code' => 'email',
                'template_group' => 'documents',
                'name_translations' => $this->translations('Документ отклонен', 'Document rejected', 'Dokumentas atmestas', 'Dokument odrzucony'),
                'description_translations' => $this->translations('Сообщение ученику о причине отклонения документа.', 'Message for a student about a rejected document.', 'Zinute mokiniui apie atmesta dokumenta.', 'Wiadomosc do ucznia o odrzuconym dokumencie.'),
                'subject_translations' => $this->translations('Документ требует исправления', 'Document needs correction', 'Dokumenta reikia pataisyti', 'Dokument wymaga poprawy'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. Документ {{ document_name }} отклонен: {{ rejection_reason }}.', 'Hello {{ student_name }}. {{ document_name }} was rejected: {{ rejection_reason }}.', 'Sveiki, {{ student_name }}. Dokumentas {{ document_name }} atmestas: {{ rejection_reason }}.', 'Dzien dobry, {{ student_name }}. Dokument {{ document_name }} odrzucono: {{ rejection_reason }}.'),
                'variables' => ['student_name' => 'string', 'document_name' => 'string', 'rejection_reason' => 'string'],
            ],
            [
                'code' => 'exam_reminder',
                'state' => 'examReminder',
                'channel_code' => 'email',
                'template_group' => 'exams',
                'name_translations' => $this->translations('Напоминание об экзамене', 'Exam reminder', 'Egzamino priminimas', 'Przypomnienie o egzaminie'),
                'description_translations' => $this->translations('Напоминание ученику о предстоящем экзамене.', 'Reminder for a student about an upcoming exam.', 'Priminimas mokiniui apie artejanti egzamina.', 'Przypomnienie uczniowi o nadchodzacym egzaminie.'),
                'subject_translations' => $this->translations('Напоминание об экзамене', 'Exam reminder', 'Egzamino priminimas', 'Przypomnienie o egzaminie'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. Экзамен назначен на {{ exam_date }}.', 'Hello {{ student_name }}. Your exam is scheduled for {{ exam_date }}.', 'Sveiki, {{ student_name }}. Jusu egzaminas suplanuotas {{ exam_date }}.', 'Dzien dobry, {{ student_name }}. Egzamin zaplanowano na {{ exam_date }}.'),
                'variables' => ['student_name' => 'string', 'exam_date' => 'date', 'exam_type' => 'string'],
            ],
            [
                'code' => 'contract_generated',
                'state' => 'contractGenerated',
                'channel_code' => 'email',
                'template_group' => 'documents',
                'name_translations' => $this->translations('Договор создан', 'Contract generated', 'Sutartis sukurta', 'Umowa utworzona'),
                'description_translations' => $this->translations('Сообщение ученику о подготовленном договоре.', 'Message for a student about a prepared contract.', 'Zinute mokiniui apie paruosta sutarti.', 'Wiadomosc do ucznia o przygotowanej umowie.'),
                'subject_translations' => $this->translations('Договор готов', 'Contract is ready', 'Sutartis paruosta', 'Umowa jest gotowa'),
                'body_translations' => $this->translations('Здравствуйте, {{ student_name }}. Договор {{ contract_number }} готов к проверке.', 'Hello {{ student_name }}. Contract {{ contract_number }} is ready for review.', 'Sveiki, {{ student_name }}. Sutartis {{ contract_number }} paruosta perziurai.', 'Dzien dobry, {{ student_name }}. Umowa {{ contract_number }} jest gotowa do sprawdzenia.'),
                'variables' => ['student_name' => 'string', 'contract_number' => 'string'],
            ],
        ];
    }

    /**
     * @param  array<string, string>  $variables
     * @return array<string, array{type: string, required: bool}>
     */
    private function schema(array $variables): array
    {
        return collect($variables)
            ->map(fn (string $type): array => ['type' => $type, 'required' => true])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
