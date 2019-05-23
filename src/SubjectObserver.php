<?php

namespace Nxmad\EloquentI18n;

use Illuminate\Database\Eloquent\Model;
use Nxmad\EloquentI18n\Models\Translation;

class SubjectObserver
{
    /**
     * Synchronize realTranslations with relation on save.
     *
     * @param Model $subject
     */
    public static function saved(Model $subject)
    {
        $all = $subject->realTranslations;

        // Walking over existing translations
        foreach ($subject->translations as $t)
        {
            if (! isset($all[$t->key][$t->locale]))
            {
                $t->delete();

                continue;
            }

            $t->value = $all[$t->key][$t->locale];
            $t->save();

            unset($all[$t->key][$t->locale]);
        }

        // Walking over remaining translations (they are probably new)
        foreach ($all as $key => $translations)
        {
            foreach ($translations as $locale => $value)
            {
                $subject->translations()
                    ->firstOrNew(compact('key', 'locale'))
                    ->fill(compact('value'))
                    ->save();
            }
        }
    }
}
