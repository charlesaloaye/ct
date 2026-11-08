<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait ValidatesSettings
{
    /**
     * Validate several settings groups sent together ({identity: {...}, hero: {...}})
     * and save each one.
     *
     * @param  list<string>  $groups
     */
    protected function saveGroups(Request $request, array $groups): void
    {
        $rules = [];
        foreach ($groups as $group) {
            $rules[$group] = array_is_list(SiteSettings::DEFAULTS[$group]) ? ['present', 'array', 'max:30'] : ['required', 'array'];
            foreach (SiteSettings::rules($group) as $field => $fieldRules) {
                $rules["{$group}.{$field}"] = $fieldRules;
            }
        }

        $validated = $request->validate($rules, [], $this->attributeNames($rules));

        foreach ($groups as $group) {
            $value = $validated[$group] ?? [];
            // Only keep keys that were validated (drops anything unexpected in the payload).
            SiteSettings::save($group, array_is_list(SiteSettings::DEFAULTS[$group]) ? $value : Arr::only($value, array_keys(SiteSettings::DEFAULTS[$group])));
        }
    }

    /** "hero.primaryUrl" -> "primary url", so validation messages read naturally. */
    private function attributeNames(array $rules): array
    {
        return collect(array_keys($rules))->mapWithKeys(fn ($key) => [
            $key => str((string) last(explode('.', str_replace('.*', '', $key))))->snake(' ')->toString(),
        ])->all();
    }
}
