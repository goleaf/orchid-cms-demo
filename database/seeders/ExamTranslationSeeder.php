<?php

namespace Database\Seeders;

use App\Models\Language;

class ExamTranslationSeeder extends SystemTranslationSeeder
{
    public function run(): void
    {
        if (! Language::query()->exists()) {
            $this->call(LanguageSeeder::class);
        }

        $this->seedEntries($this->entries());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entries(): array
    {
        return [
            $this->entry('menu', 'menu.exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),

            $this->entry('permissions', 'permissions.exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('permissions', 'permissions.exams.view', ['ru' => 'Просмотр экзаменов', 'en' => 'View exams', 'lt' => 'Perziureti egzaminus', 'pl' => 'Podglad egzaminow']),
            $this->entry('permissions', 'permissions.exams.manage_admissions', ['ru' => 'Управление допусками', 'en' => 'Manage admissions', 'lt' => 'Tvarkyti leidimus', 'pl' => 'Zarzadzanie dopuszczeniami']),
            $this->entry('permissions', 'permissions.exams.manage_sessions', ['ru' => 'Управление сессиями', 'en' => 'Manage sessions', 'lt' => 'Tvarkyti sesijas', 'pl' => 'Zarzadzanie sesjami']),
            $this->entry('permissions', 'permissions.exams.record_results', ['ru' => 'Внесение результатов', 'en' => 'Record results', 'lt' => 'Ivesti rezultatus', 'pl' => 'Wprowadzanie wynikow']),
            $this->entry('permissions', 'permissions.exams.schedule_retakes', ['ru' => 'Назначение пересдач', 'en' => 'Schedule retakes', 'lt' => 'Planuoti perlaikymus', 'pl' => 'Planowanie poprawek']),
            $this->entry('permissions', 'permissions.exams.view_activities', ['ru' => 'Просмотр истории экзаменов', 'en' => 'View exam history', 'lt' => 'Perziureti egzaminu istorija', 'pl' => 'Podglad historii egzaminow']),

            $this->entry('operations', 'operations.exams.title', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('operations', 'operations.exams.description', ['ru' => 'Допуски, экзаменационные сессии, попытки, результаты и пересдачи.', 'en' => 'Admissions, exam sessions, attempts, results, and retakes.', 'lt' => 'Leidimai, egzaminu sesijos, bandymai, rezultatai ir perlaikymai.', 'pl' => 'Dopuszczenia, sesje egzaminacyjne, proby, wyniki i poprawki.']),

            $this->entry('exams', 'exams.title', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('exams', 'exams.description', ['ru' => 'Готовность, документы, оплаты и попытки экзаменов связаны с учеником.', 'en' => 'Readiness, documents, payments, and exam attempts stay linked to the student.', 'lt' => 'Pasiruosimas, dokumentai, mokejimai ir egzamino bandymai susiejami su mokiniu.', 'pl' => 'Gotowosc, dokumenty, platnosci i proby egzaminow sa powiazane z uczniem.']),
            $this->entry('exams', 'exams.sessions.title', ['ru' => 'Экзаменационные сессии', 'en' => 'Exam sessions', 'lt' => 'Egzaminu sesijos', 'pl' => 'Sesje egzaminacyjne']),
            $this->entry('exams', 'exams.empty.no_sessions', ['ru' => 'Сессии экзаменов не найдены.', 'en' => 'No exam sessions found.', 'lt' => 'Egzaminu sesiju nerasta.', 'pl' => 'Nie znaleziono sesji egzaminacyjnych.']),
            $this->entry('exams', 'exams.columns.starts_at', ['ru' => 'Начало', 'en' => 'Starts at', 'lt' => 'Pradzia', 'pl' => 'Poczatek']),
            $this->entry('exams', 'exams.columns.student', ['ru' => 'Ученик', 'en' => 'Student', 'lt' => 'Mokinys', 'pl' => 'Uczen']),
            $this->entry('exams', 'exams.columns.type', ['ru' => 'Тип', 'en' => 'Type', 'lt' => 'Tipas', 'pl' => 'Typ']),
            $this->entry('exams', 'exams.columns.provider', ['ru' => 'Провайдер', 'en' => 'Provider', 'lt' => 'Teikejas', 'pl' => 'Operator']),
            $this->entry('exams', 'exams.columns.group', ['ru' => 'Группа', 'en' => 'Group', 'lt' => 'Grupe', 'pl' => 'Grupa']),
            $this->entry('exams', 'exams.columns.program', ['ru' => 'Программа', 'en' => 'Program', 'lt' => 'Programa', 'pl' => 'Program szkolenia']),
            $this->entry('exams', 'exams.columns.instructor', ['ru' => 'Инструктор', 'en' => 'Instructor', 'lt' => 'Instruktorius', 'pl' => 'Instruktor']),
            $this->entry('exams', 'exams.columns.capacity', ['ru' => 'Места', 'en' => 'Seats', 'lt' => 'Vietos', 'pl' => 'Miejsca']),
            $this->entry('exams', 'exams.columns.attempts', ['ru' => 'Попытки', 'en' => 'Attempts', 'lt' => 'Bandymai', 'pl' => 'Proby']),
            $this->entry('exams', 'exams.columns.admission_status', ['ru' => 'Статус допуска', 'en' => 'Admission status', 'lt' => 'Leidimo busena', 'pl' => 'Status dopuszczenia']),
            $this->entry('exams', 'exams.columns.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Stan']),
            $this->entry('exams', 'exams.columns.result', ['ru' => 'Результат', 'en' => 'Result', 'lt' => 'Rezultatas', 'pl' => 'Wynik']),
            $this->entry('exams', 'exams.columns.score', ['ru' => 'Баллы', 'en' => 'Score', 'lt' => 'Taskai', 'pl' => 'Punkty']),
            $this->entry('exams', 'exams.columns.location', ['ru' => 'Место', 'en' => 'Location', 'lt' => 'Vieta', 'pl' => 'Miejsce']),

            $this->entry('exams', 'exams.types.internal_theory', ['ru' => 'Внутренний теория', 'en' => 'Internal theory', 'lt' => 'Vidinis teorijos', 'pl' => 'Wewnetrzny teoretyczny']),
            $this->entry('exams', 'exams.types.internal_practical', ['ru' => 'Внутренний практика', 'en' => 'Internal practical', 'lt' => 'Vidinis praktikos', 'pl' => 'Wewnetrzny praktyczny']),
            $this->entry('exams', 'exams.types.official_theory_placeholder', ['ru' => 'Официальный теория заготовка', 'en' => 'Official theory placeholder', 'lt' => 'Oficialaus teorijos egzamino irasas', 'pl' => 'Oficjalny teoretyczny wpis']),
            $this->entry('exams', 'exams.types.official_practical_placeholder', ['ru' => 'Официальный практика заготовка', 'en' => 'Official practical placeholder', 'lt' => 'Oficialaus praktikos egzamino irasas', 'pl' => 'Oficjalny praktyczny wpis']),
            $this->entry('exams', 'exams.types.state_theory', ['ru' => 'Государственный теория', 'en' => 'State theory', 'lt' => 'Valstybinis teorijos', 'pl' => 'Panstwowy teoretyczny']),
            $this->entry('exams', 'exams.types.state_practical', ['ru' => 'Государственный практика', 'en' => 'State practical', 'lt' => 'Valstybinis praktikos', 'pl' => 'Panstwowy praktyczny']),

            $this->entry('exams', 'exams.providers.internal', ['ru' => 'Автошкола', 'en' => 'Internal', 'lt' => 'Vidinis', 'pl' => 'Wewnetrzny']),
            $this->entry('exams', 'exams.providers.state', ['ru' => 'Государственный', 'en' => 'State', 'lt' => 'Valstybinis', 'pl' => 'Panstwowy']),

            $this->entry('exams', 'exams.session_statuses.draft', ['ru' => 'Черновик', 'en' => 'Draft', 'lt' => 'Juodrastis', 'pl' => 'Szkic']),
            $this->entry('exams', 'exams.session_statuses.planned', ['ru' => 'Запланирована', 'en' => 'Planned', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana']),
            $this->entry('exams', 'exams.session_statuses.scheduled', ['ru' => 'Запланирована', 'en' => 'Scheduled', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana']),
            $this->entry('exams', 'exams.session_statuses.open', ['ru' => 'Открыта', 'en' => 'Open', 'lt' => 'Atvira', 'pl' => 'Otwarta']),
            $this->entry('exams', 'exams.session_statuses.full', ['ru' => 'Заполнена', 'en' => 'Full', 'lt' => 'Pilna', 'pl' => 'Pelna']),
            $this->entry('exams', 'exams.session_statuses.in_progress', ['ru' => 'Идет', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku']),
            $this->entry('exams', 'exams.session_statuses.completed', ['ru' => 'Завершена', 'en' => 'Completed', 'lt' => 'Baigta', 'pl' => 'Zakonczona']),
            $this->entry('exams', 'exams.session_statuses.cancelled', ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana']),
            $this->entry('exams', 'exams.session_statuses.archived', ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvuota', 'pl' => 'Zarchiwizowana']),

            $this->entry('exams', 'exams.admission_statuses.draft', ['ru' => 'Черновик', 'en' => 'Draft', 'lt' => 'Juodrastis', 'pl' => 'Szkic']),
            $this->entry('exams', 'exams.admission_statuses.checking', ['ru' => 'Проверка', 'en' => 'Checking', 'lt' => 'Tikrinama', 'pl' => 'Sprawdzanie']),
            $this->entry('exams', 'exams.admission_statuses.ready', ['ru' => 'Готов', 'en' => 'Ready', 'lt' => 'Paruosta', 'pl' => 'Gotowe']),
            $this->entry('exams', 'exams.admission_statuses.admitted', ['ru' => 'Допущен', 'en' => 'Admitted', 'lt' => 'Leista', 'pl' => 'Dopuszczony']),
            $this->entry('exams', 'exams.admission_statuses.blocked', ['ru' => 'Заблокирован', 'en' => 'Blocked', 'lt' => 'Uzblokuota', 'pl' => 'Zablokowane']),
            $this->entry('exams', 'exams.admission_statuses.expired', ['ru' => 'Истек', 'en' => 'Expired', 'lt' => 'Pasibaige', 'pl' => 'Wygaslo']),
            $this->entry('exams', 'exams.admission_statuses.cancelled', ['ru' => 'Отменен', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowane']),
            $this->entry('exams', 'exams.admission_statuses.passed', ['ru' => 'Сдано', 'en' => 'Passed', 'lt' => 'Islaikyta', 'pl' => 'Zdane']),
            $this->entry('exams', 'exams.admission_statuses.failed', ['ru' => 'Не сдано', 'en' => 'Failed', 'lt' => 'Neislaikyta', 'pl' => 'Niezdane']),
            $this->entry('exams', 'exams.admission_statuses.retake_required', ['ru' => 'Нужна пересдача', 'en' => 'Retake required', 'lt' => 'Reikia perlaikymo', 'pl' => 'Wymagana poprawka']),
            $this->entry('exams', 'exams.admission_statuses.retake_scheduled', ['ru' => 'Пересдача назначена', 'en' => 'Retake scheduled', 'lt' => 'Perlaikymas suplanuotas', 'pl' => 'Poprawka zaplanowana']),

            $this->entry('exams', 'exams.attempt_statuses.scheduled', ['ru' => 'Назначена', 'en' => 'Scheduled', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana']),
            $this->entry('exams', 'exams.attempt_statuses.in_progress', ['ru' => 'Идет', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku']),
            $this->entry('exams', 'exams.attempt_statuses.passed', ['ru' => 'Сдан', 'en' => 'Passed', 'lt' => 'Islaikyta', 'pl' => 'Zdany']),
            $this->entry('exams', 'exams.attempt_statuses.failed', ['ru' => 'Не сдан', 'en' => 'Failed', 'lt' => 'Neislaikyta', 'pl' => 'Niezdany']),
            $this->entry('exams', 'exams.attempt_statuses.no_show', ['ru' => 'Не явился', 'en' => 'No-show', 'lt' => 'Neatvyko', 'pl' => 'Nieobecnosc']),
            $this->entry('exams', 'exams.attempt_statuses.cancelled', ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana']),

            $this->entry('exams', 'exams.checklist.statuses.pending', ['ru' => 'Ожидает', 'en' => 'Pending', 'lt' => 'Laukiama', 'pl' => 'Oczekuje']),
            $this->entry('exams', 'exams.checklist.statuses.passed', ['ru' => 'Готово', 'en' => 'Passed', 'lt' => 'Paruosta', 'pl' => 'Zaliczone']),
            $this->entry('exams', 'exams.checklist.statuses.failed', ['ru' => 'Ошибка', 'en' => 'Failed', 'lt' => 'Nepavyko', 'pl' => 'Niepowodzenie']),
            $this->entry('exams', 'exams.checklist.statuses.waived', ['ru' => 'Не требуется', 'en' => 'Waived', 'lt' => 'Nereikia', 'pl' => 'Zniesione']),
            $this->entry('exams', 'exams.checklist.items.identity_document', ['ru' => 'Документ личности', 'en' => 'Identity document', 'lt' => 'Asmens dokumentas', 'pl' => 'Dokument tozsamosci']),
            $this->entry('exams', 'exams.checklist.items.medical_certificate', ['ru' => 'Медицинская справка', 'en' => 'Medical certificate', 'lt' => 'Medicinos pazyma', 'pl' => 'Zaswiadczenie lekarskie']),
            $this->entry('exams', 'exams.checklist.items.training_contract', ['ru' => 'Договор обучения', 'en' => 'Training contract', 'lt' => 'Mokymo sutartis', 'pl' => 'Umowa szkoleniowa']),
            $this->entry('exams', 'exams.checklist.items.payment_clearance', ['ru' => 'Оплата', 'en' => 'Payment clearance', 'lt' => 'Mokejimo patikra', 'pl' => 'Rozliczenie platnosci']),
            $this->entry('exams', 'exams.checklist.items.theory_hours', ['ru' => 'Часы теории', 'en' => 'Theory hours', 'lt' => 'Teorijos valandos', 'pl' => 'Godziny teorii']),
            $this->entry('exams', 'exams.checklist.items.practice_hours', ['ru' => 'Часы практики', 'en' => 'Practice hours', 'lt' => 'Praktikos valandos', 'pl' => 'Godziny praktyki']),

            $this->entry('exams', 'exams.activities.types.admission_saved', ['ru' => 'Допуск сохранен', 'en' => 'Admission saved', 'lt' => 'Leidimas issaugotas', 'pl' => 'Dopuszczenie zapisane']),
            $this->entry('exams', 'exams.activities.types.session_scheduled', ['ru' => 'Сессия назначена', 'en' => 'Session scheduled', 'lt' => 'Sesija suplanuota', 'pl' => 'Sesja zaplanowana']),
            $this->entry('exams', 'exams.activities.types.attempt_recorded', ['ru' => 'Попытка записана', 'en' => 'Attempt recorded', 'lt' => 'Bandymas irasytas', 'pl' => 'Proba zapisana']),
            $this->entry('exams', 'exams.activities.types.retake_scheduled', ['ru' => 'Пересдача назначена', 'en' => 'Retake scheduled', 'lt' => 'Perlaikymas suplanuotas', 'pl' => 'Poprawka zaplanowana']),
            $this->entry('exams', 'exams.activities.titles.admission_saved', ['ru' => 'Допуск к экзамену сохранен', 'en' => 'Exam admission saved', 'lt' => 'Egzamino leidimas issaugotas', 'pl' => 'Dopuszczenie do egzaminu zapisane']),
            $this->entry('exams', 'exams.activities.titles.session_scheduled', ['ru' => 'Экзаменационная сессия назначена', 'en' => 'Exam session scheduled', 'lt' => 'Egzamino sesija suplanuota', 'pl' => 'Sesja egzaminacyjna zaplanowana']),
            $this->entry('exams', 'exams.activities.titles.attempt_recorded', ['ru' => 'Результат попытки записан', 'en' => 'Exam attempt result recorded', 'lt' => 'Egzamino bandymo rezultatas irasytas', 'pl' => 'Wynik proby egzaminacyjnej zapisany']),
            $this->entry('exams', 'exams.activities.titles.retake_scheduled', ['ru' => 'Пересдача экзамена назначена', 'en' => 'Exam retake scheduled', 'lt' => 'Egzamino perlaikymas suplanuotas', 'pl' => 'Poprawka egzaminu zaplanowana']),

            $this->entry('exams', 'exams.messages.admission_saved', ['ru' => 'Допуск сохранен.', 'en' => 'Admission saved.', 'lt' => 'Leidimas issaugotas.', 'pl' => 'Dopuszczenie zapisane.']),
            $this->entry('exams', 'exams.messages.session_scheduled', ['ru' => 'Сессия назначена.', 'en' => 'Session scheduled.', 'lt' => 'Sesija suplanuota.', 'pl' => 'Sesja zaplanowana.']),
            $this->entry('exams', 'exams.messages.attempt_recorded', ['ru' => 'Результат сохранен.', 'en' => 'Result recorded.', 'lt' => 'Rezultatas issaugotas.', 'pl' => 'Wynik zapisany.']),
            $this->entry('exams', 'exams.messages.retake_scheduled', ['ru' => 'Пересдача назначена.', 'en' => 'Retake scheduled.', 'lt' => 'Perlaikymas suplanuotas.', 'pl' => 'Poprawka zaplanowana.']),

            $this->entry('exams', 'exams.validation.invalid_exam_type', ['ru' => 'Недопустимый тип экзамена.', 'en' => 'Invalid exam type.', 'lt' => 'Netinkamas egzamino tipas.', 'pl' => 'Nieprawidlowy typ egzaminu.']),
            $this->entry('exams', 'exams.validation.admission_not_ready', ['ru' => 'Допуск к экзамену еще не готов.', 'en' => 'The exam admission is not ready.', 'lt' => 'Egzamino leidimas dar neparuostas.', 'pl' => 'Dopuszczenie do egzaminu nie jest gotowe.']),
            $this->entry('exams', 'exams.validation.session_full_or_closed', ['ru' => 'Сессия закрыта или заполнена.', 'en' => 'The exam session is full or closed.', 'lt' => 'Egzamino sesija pilna arba uzdaryta.', 'pl' => 'Sesja egzaminacyjna jest pelna albo zamknieta.']),
            $this->entry('exams', 'exams.validation.attempt_cannot_be_retaken', ['ru' => 'Эту попытку нельзя отправить на пересдачу.', 'en' => 'This attempt cannot be retaken.', 'lt' => 'Sio bandymo negalima perlaikyti.', 'pl' => 'Tej proby nie mozna poprawic.']),
            $this->entry('exams', 'exams.validation.attempt_already_has_open_retake', ['ru' => 'Для этой попытки уже есть открытая пересдача.', 'en' => 'This attempt already has an open retake.', 'lt' => 'Sis bandymas jau turi atvira perlaikyma.', 'pl' => 'Ta proba ma juz otwarta poprawke.']),
            $this->entry('exams', 'exams.validation.capacity_below_taken_seats', ['ru' => 'Вместимость не может быть меньше занятых мест.', 'en' => 'Capacity cannot be below taken seats.', 'lt' => 'Talpa negali buti mazesne uz uzimtas vietas.', 'pl' => 'Pojemnosc nie moze byc mniejsza niz zajete miejsca.']),
        ];
    }
}
