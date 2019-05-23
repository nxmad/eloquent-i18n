# Eloquent-i18n
Internationalization helper for Eloquent models.

### Installation
1. Pull the latest version using composer:
    ```bash
    composer require nxmad/eloquent-i18n
    ```
2. Publish database migration:
    ```bash
    php artisan vendor:publish --provider="Nxmad\EloquentI18n\EloquentI18nProvider"
    ```
3. Use trait in your models:
    ```php
    use Nxmad\EloquentI18n\Traits\HasTranslations;
 
    class Something extends Model
    {
        use HasTranslations;

        // ...
    }    
    ```

### Usage guide
Add translations:
```php
$translations = [
    'title' => [
        'en' => 'Hello, world!',
        'de' => 'Hallo Welt!',
    ],
    
    'content' => [
        'en' => 'This is my page content.',
        'de' => 'Dies ist mein Seiteninhalt.',
    ]
];

$page = new Page();

// First way
$page->translations = $translations;

// Alt. way
$page->addTranslations($translations);
$page->addTranslations('title', 'Hello, world!'); // would add translation for current locale
$page->addTranslations('title', 'Hello, world!', 'en'); // would add translation for specified locale

$page->save();
```

Remove translations:
```php
// Remove all existing translations
$page->removeTranslations();

// Remove by the key
$page->removeTranslations('title');

// Remove by locale
$page->removeTranslations(null, 'de');

// Or both:
$page->removeTranslations('title', 'de');
```

Get translated value:
```php
app()->setLocale('de');
echo $page->title; // Hallo Welt!

app()->setLocale('en');
echo $page->title; // Hello, world!

// alt. way
$page->t('title', 'default value', 'locale');

// get all translations for key
$page->tAll('title');

// serialize
echo $page->toJson(); // { id, title, content, etc. }
```
