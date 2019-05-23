<?php

namespace Nxmad\EloquentI18n\Traits;

use Nxmad\EloquentI18n\SubjectObserver;
use Illuminate\Database\Eloquent\Builder;
use Nxmad\EloquentI18n\Models\Translation;

trait HasTranslations
{
    /**
     * The list of translations.
     *
     * @var array
     */
    public $realTranslations = [];

    /**
     * Determines if original translations (relations) will hidden from JSON.
     *
     * @var bool
     */
    protected $hideTranslations = true;

    /**
     * Determines if all existing translations will be serialized to JSON.
     * Otherwise only one translation will be chosen according to current app locale.
     *
     * @var bool
     */
    protected $serializeAllTranslations = false;

    /**
     * Eloquent boot hook.
     */
    public static function bootHasTranslations()
    {
        static::observe(SubjectObserver::class);
        static::addGlobalScope('translations', function (Builder $builder) {
            $builder->with('translations');
        });
    }

    /**
     * Main relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'subject');
    }

    /**
     * Get existing translation..
     *
     * @param $key
     * @param mixed $default
     * @param string $locale
     *
     * @return string
     */
    public function t($key, $default = null, $locale = null)
    {
        $locale = $locale ?? $this->getLocale();
        $fallback = $this->getFallbackLocale();

        if (! isset($this->realTranslations[$key])) {
            return $default;
        }

        return $this->realTranslations[$key][$locale] ?? ($this->realTranslations[$key][$fallback] ?? $default);
    }

    /**
     * Get all existing translations for given key.
     *
     * @param string $key
     * @param array $default
     *
     * @return array
     */
    public function tAll(string $key, $default = [])
    {
        return $this->realTranslations[$key] ?? $default;
    }

    /**
     * Add translation for specified key.
     *
     * @param string|array $key
     * @param array|null $value
     * @param string|null $locale
     *
     * @return $this
     * @throws
     */
    public function addTranslations($key, $value = null, string $locale = null)
    {
        if (is_array($key))
        {
            $this->translations = $key;

            return $this;
        }

        $locale = $locale ?? $this->getLocale();

        if (! is_array($value)) {
            $value = [$locale => $value];
        }

        $this->realTranslations[$key] = array_merge($this->realTranslations[$key] ?? [], $value);

        return $this;
    }

    /**
     * Remove existing translations.
     *
     * @param string|null $key
     * @param string|null $locale
     *
     * @return $this
     */
    public function removeTranslations(string $key = null, string $locale = null)
    {
        if (! ($key || $locale)) {
            $this->realTranslations = [];
        }

        if ($key && ! $locale) {
            unset($this->realTranslations[$key]);
        }

        if ($key && $locale) {
            unset($this->realTranslations[$key][$locale]);
        }

        if (! $key && $locale)
        {
            foreach ($this->realTranslations as $k => $translations) {
                unset($this->realTranslations[$k][$locale]);
            }
        }

        return $this;
    }

    /**
     * Get hook.
     *
     * @param $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->t($key) ?? parent::getAttribute($key);
    }

    /**
     * Set hook.
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     * @throws \Throwable
     */
    public function setAttribute($key, $value)
    {
        if ($key === 'translations')
        {
            throw_unless(is_array($value), \RuntimeException::class, '`translations` should be array');

            foreach ($value as $key => $translations) {
                $this->addTranslations($key, $translations);
            }

            return;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Set relation hook.
     *
     * @param  string  $relation
     * @param  mixed  $value
     *
     * @return $this
     * @throws
     */
    public function setRelation($relation, $value)
    {
        parent::setRelation($relation, $value);

        if ($relation === 'translations')
        {
            foreach ($value as $t) {
                $this->realTranslations[$t->key][$t->locale] = $t->value;
            }

            $this->makeHidden('translations');
        }

        return $this;
    }

    /**
     * Convert model instance to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $translatedAttributes = [];

        foreach (array_keys($this->realTranslations) as $key) {
            $translatedAttributes[$key] = $this->serializeAllTranslations ? $this->tAll($key) : $this->t($key);
        }

        return array_merge(parent::jsonSerialize(), $translatedAttributes);
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        return app()->getLocale();
    }

    /**
     * @return mixed
     */
    private function getFallbackLocale()
    {
        return app('config')->get('app.fallback_locale');
    }
}
