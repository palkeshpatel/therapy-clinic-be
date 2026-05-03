<?php

namespace App\Services;

use Carbon\Carbon;

class HolidayCalendarService
{
    public function generateForYear(int $year, bool $includeSpecialDates = false): array
    {
        $rows = [];

        foreach ($this->fixedRecurringTemplates() as $template) {
            $date = Carbon::create($year, $template['month'], $template['day'])->toDateString();
            $this->mergeRow($rows, $date, [
                'holiday_date' => $date,
                'holiday_name' => $template['holiday_name'],
                'holiday_type' => $template['holiday_type'],
                'applicable' => $template['applicable'],
                'description' => $template['description'],
                'rule_type' => 'fixed_yearly',
                'is_recurring' => true,
                'status' => 'active',
            ]);
        }

        foreach ($this->weeklyRules($year) as $row) {
            $this->mergeRow($rows, $row['holiday_date'], $row);
        }

        if ($includeSpecialDates) {
            foreach ($this->specialDates2026() as $template) {
                if ((int) $template['year'] !== $year) {
                    continue;
                }

                $date = Carbon::create($template['year'], $template['month'], $template['day'])->toDateString();
                $this->mergeRow($rows, $date, [
                    'holiday_date' => $date,
                    'holiday_name' => $template['holiday_name'],
                    'holiday_type' => $template['holiday_type'],
                    'applicable' => $template['applicable'],
                    'description' => $template['description'],
                    'rule_type' => 'one_time',
                    'is_recurring' => false,
                    'status' => 'active',
                ]);
            }
        }

        ksort($rows);
        return array_values($rows);
    }

    private function fixedRecurringTemplates(): array
    {
        return [
            ['month' => 1, 'day' => 1, 'holiday_name' => 'New Year', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'New Year day'],
            ['month' => 1, 'day' => 26, 'holiday_name' => 'Republic Day', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Republic Day of India'],
            ['month' => 4, 'day' => 14, 'holiday_name' => 'Ambedkar Jayanti', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Dr. B. R. Ambedkar Jayanti'],
            ['month' => 5, 'day' => 1, 'holiday_name' => 'Labour Day', 'holiday_type' => 'State', 'applicable' => 'Some States', 'description' => 'Labour Day / May Day'],
            ['month' => 8, 'day' => 15, 'holiday_name' => 'Independence Day', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Independence Day of India'],
            ['month' => 10, 'day' => 2, 'holiday_name' => 'Gandhi Jayanti', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Mahatma Gandhi Jayanti'],
            ['month' => 12, 'day' => 25, 'holiday_name' => 'Christmas', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Christmas Day'],
            ['month' => 11, 'day' => 13, 'holiday_name' => 'Gujarati New Year', 'holiday_type' => 'State', 'applicable' => 'Gujarat', 'description' => 'Gujarati New Year'],
        ];
    }

    private function specialDates2026(): array
    {
        return [
            ['year' => 2026, 'month' => 1, 'day' => 14, 'holiday_name' => 'Uttarayan / Makar Sankranti', 'holiday_type' => 'State', 'applicable' => 'Gujarat', 'description' => 'Festival of Uttarayan / Makar Sankranti'],
            ['year' => 2026, 'month' => 2, 'day' => 18, 'holiday_name' => 'Maha Shivratri', 'holiday_type' => 'State', 'applicable' => 'Gujarat', 'description' => 'Maha Shivratri'],
            ['year' => 2026, 'month' => 3, 'day' => 3, 'holiday_name' => 'Holi', 'holiday_type' => 'State', 'applicable' => 'Most States', 'description' => 'Festival of Holi'],
            ['year' => 2026, 'month' => 3, 'day' => 30, 'holiday_name' => 'Ram Navami', 'holiday_type' => 'State', 'applicable' => 'Many States', 'description' => 'Ram Navami'],
            ['year' => 2026, 'month' => 4, 'day' => 3, 'holiday_name' => 'Good Friday', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Good Friday'],
            ['year' => 2026, 'month' => 5, 'day' => 21, 'holiday_name' => 'Buddha Purnima', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Buddha Purnima'],
            ['year' => 2026, 'month' => 6, 'day' => 29, 'holiday_name' => 'Bakrid (Eid al-Adha)', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Bakrid / Eid al-Adha'],
            ['year' => 2026, 'month' => 7, 'day' => 17, 'holiday_name' => 'Muharram', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Muharram'],
            ['year' => 2026, 'month' => 8, 'day' => 25, 'holiday_name' => 'Janmashtami', 'holiday_type' => 'State', 'applicable' => 'Gujarat', 'description' => 'Janmashtami'],
            ['year' => 2026, 'month' => 9, 'day' => 17, 'holiday_name' => 'Ganesh Chaturthi', 'holiday_type' => 'State', 'applicable' => 'Many States', 'description' => 'Ganesh Chaturthi'],
            ['year' => 2026, 'month' => 10, 'day' => 24, 'holiday_name' => 'Dussehra', 'holiday_type' => 'State', 'applicable' => 'Most States', 'description' => 'Dussehra'],
            ['year' => 2026, 'month' => 11, 'day' => 12, 'holiday_name' => 'Diwali', 'holiday_type' => 'National', 'applicable' => 'All India', 'description' => 'Diwali'],
        ];
    }

    private function weeklyRules(int $year): array
    {
        $rows = [];
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isSunday()) {
                $rows[$date->toDateString()] = [
                    'holiday_date' => $date->toDateString(),
                    'holiday_name' => 'Sunday Holiday',
                    'holiday_type' => 'Weekly',
                    'applicable' => 'All Staff',
                    'description' => 'Weekly off on Sunday',
                    'rule_type' => 'weekly_sunday',
                    'is_recurring' => true,
                    'status' => 'active',
                ];
                continue;
            }

            if ($date->isSaturday()) {
                $weekOfMonth = (int) ceil($date->day / 7);
                if (in_array($weekOfMonth, [2, 4], true)) {
                    $label = $weekOfMonth === 2 ? '2nd Saturday Holiday' : '4th Saturday Holiday';
                    $rows[$date->toDateString()] = [
                        'holiday_date' => $date->toDateString(),
                        'holiday_name' => $label,
                        'holiday_type' => 'Weekly',
                        'applicable' => 'All Staff',
                        'description' => 'Weekly off on '.$label,
                        'rule_type' => 'weekly_saturday',
                        'is_recurring' => true,
                        'status' => 'active',
                    ];
                }
            }
        }

        return array_values($rows);
    }

    private function mergeRow(array &$rows, string $date, array $row): void
    {
        if (! isset($rows[$date])) {
            $rows[$date] = $row;
            return;
        }

        $existing = $rows[$date];
        $existing['holiday_name'] = $this->mergeText($existing['holiday_name'], $row['holiday_name']);
        $existing['holiday_type'] = $this->preferType($existing['holiday_type'], $row['holiday_type']);
        $existing['applicable'] = $this->mergeText($existing['applicable'], $row['applicable']);
        $existing['description'] = $this->mergeText($existing['description'] ?? '', $row['description'] ?? '');
        $existing['rule_type'] = $existing['rule_type'] === 'one_time' ? $row['rule_type'] : $existing['rule_type'];
        $existing['is_recurring'] = (bool) ($existing['is_recurring'] || $row['is_recurring']);
        $rows[$date] = $existing;
    }

    private function mergeText(string $a, string $b): string
    {
        $values = array_filter(array_map('trim', explode(' / ', $a.' / '.$b)));
        $values = array_values(array_unique($values));
        return implode(' / ', $values);
    }

    private function preferType(string $a, string $b): string
    {
        if ($a === 'National' || $b === 'National') {
            return 'National';
        }

        return $a !== '' ? $a : $b;
    }
}
