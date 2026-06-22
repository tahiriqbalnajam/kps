<?php

namespace App\Ai\Tools\Concerns;

use App\Models\Classes;
use App\Models\Section;
use Carbon\Carbon;

/**
 * Shared read-only helpers for the school data tools.
 *
 * All queries run on the connection already selected by SubdomainDatabaseSwitcher,
 * so results are automatically scoped to the current tenant.
 */
trait SchoolQueryHelper
{
    /**
     * Resolve a class name (exact, case-insensitive, then partial) to its id.
     */
    protected function resolveClassId(?string $name): ?int
    {
        if (blank($name)) {
            return null;
        }

        $name = trim($name);

        // Bypass the Classes 'priority' global scope — some tenant DBs lack the
        // priority column, which would otherwise make every Classes query throw.
        $class = Classes::withoutGlobalScope('priority')->whereRaw('LOWER(name) = ?', [strtolower($name)])->first()
            ?? Classes::withoutGlobalScope('priority')->where('name', 'like', "%{$name}%")->first();

        return $class?->id;
    }

    /**
     * Resolve a section name to its id, optionally scoped to a class.
     */
    protected function resolveSectionId(?string $name, ?int $classId = null): ?int
    {
        if (blank($name)) {
            return null;
        }

        $name = trim($name);

        $query = Section::whereRaw('LOWER(name) = ?', [strtolower($name)]);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $section = $query->first()
            ?? Section::where('name', 'like', "%{$name}%")->when($classId, fn ($q) => $q->where('class_id', $classId))->first();

        return $section?->id;
    }

    /**
     * Parse a date string, falling back to the given default.
     */
    protected function parseDate(?string $value, Carbon $default): Carbon
    {
        if (blank($value)) {
            return $default;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return $default;
        }
    }
}