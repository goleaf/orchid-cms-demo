@extends('site.layout')

@section('content')
    <main>
        <section class="section dark">
            <div class="section-inner">
                <p class="kicker">Online enrollment</p>
                <h1>Запись в автошколу</h1>
                <p class="lead">Заявка создает лид в CRM, сохраняет UTM-метки, источник, документы и предпочтения ученика.</p>
            </div>
        </section>

        <section class="section" id="application-form">
            <div class="section-inner">
                @if (session('status'))
                    <p class="notice">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('site.apply.store') }}" enctype="multipart/form-data" class="card">
                    @csrf

                    <input type="hidden" name="source" value="{{ old('source', $tracking['source']) }}">
                    <input type="hidden" name="utm_source" value="{{ old('utm_source', $tracking['utm_source']) }}">
                    <input type="hidden" name="utm_medium" value="{{ old('utm_medium', $tracking['utm_medium']) }}">
                    <input type="hidden" name="utm_campaign" value="{{ old('utm_campaign', $tracking['utm_campaign']) }}">
                    <input type="hidden" name="utm_term" value="{{ old('utm_term', $tracking['utm_term']) }}">
                    <input type="hidden" name="utm_content" value="{{ old('utm_content', $tracking['utm_content']) }}">
                    <input type="hidden" name="referrer_url" value="{{ old('referrer_url', $tracking['referrer_url']) }}">

                    <div class="form-grid">
                        <label>
                            Категория
                            <select name="training_program_id" required>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}" @selected((string) old('training_program_id', $tracking['program']) === (string) $program->id)>
                                        {{ $program->title }} · {{ $program->priceForHumans() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('training_program_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Филиал
                            <select name="branch_id" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>
                                        {{ $branch->city }} · {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Группа
                            <select name="training_group_id">
                                <option value="">Подобрать менеджером</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" @selected((string) old('training_group_id') === (string) $group->id)>
                                        {{ $group->code }} · {{ $group->trainingProgram->title }} · {{ $group->branch->city }}
                                    </option>
                                @endforeach
                            </select>
                            @error('training_group_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Инструктор
                            <select name="instructor_id">
                                <option value="">Без предпочтения</option>
                                @foreach ($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" @selected((string) old('instructor_id', $tracking['instructor']) === (string) $instructor->id)>
                                        {{ $instructor->name }} · {{ $instructor->branch->city }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor_id') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Формат
                            <select name="preferred_format" required>
                                @foreach ($formats as $value => $label)
                                    <option value="{{ $value }}" @selected(old('preferred_format', 'mixed') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('preferred_format') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Язык обучения
                            <select name="preferred_language" required>
                                @foreach ($languages as $language)
                                    <option value="{{ $language }}" @selected(old('preferred_language', 'Lithuanian') === $language)>{{ $language }}</option>
                                @endforeach
                            </select>
                            @error('preferred_language') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Имя
                            <input name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Фамилия
                            <input name="last_name" value="{{ old('last_name') }}">
                            @error('last_name') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Email
                            <input type="email" name="email" value="{{ old('email') }}">
                            @error('email') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Телефон
                            <input name="phone" value="{{ old('phone') }}">
                            @error('phone') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Мессенджер
                            <input name="messenger" value="{{ old('messenger') }}" placeholder="WhatsApp, Telegram, Viber">
                            @error('messenger') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Город
                            <input name="city" value="{{ old('city') }}" placeholder="Vilnius">
                            @error('city') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Предпочтительное время
                            <input name="preferred_time" value="{{ old('preferred_time') }}" placeholder="Evenings, weekends, mornings">
                            @error('preferred_time') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Бюджет EUR
                            <input type="number" min="0" step="10" name="budget_eur" value="{{ old('budget_eur') }}">
                            @error('budget_eur') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            Документы
                            <input type="file" name="documents[]" multiple>
                            @error('documents') <span class="error">{{ $message }}</span> @enderror
                            @error('documents.*') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label class="full">
                            Комментарий
                            <textarea name="message">{{ old('message') }}</textarea>
                            @error('message') <span class="error">{{ $message }}</span> @enderror
                        </label>

                        <label class="full">
                            <span>
                                <input type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required>
                                Согласен на обработку данных
                            </span>
                            @error('privacy_consent') <span class="error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">Отправить заявку</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection
