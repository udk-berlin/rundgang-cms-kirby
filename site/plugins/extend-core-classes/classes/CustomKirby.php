<?php

// guide: https://getkirby.com/docs/cookbook/development-deployment/replacing-core-classes

// give it a namespace, not required but good practice

namespace cookbook\core;

// import App class
// (so that we don't have to use fully-qualified class names all over the place)
use Kirby\Cms\App as Kirby;
//use Kirby\Cms\Languages;
use Kirby\Cms\Translation;
use Kirby\Cms\Translations;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class CustomKirby extends Kirby
{
    public function translations(): Translations
    {
        if ($this->translations instanceof Translations) {
            return $this->translations;
        }

        $translations = $this->extensions['translations'] ?? [];

        // injects languages translations
        if ($languages = $this->languages()) {
            foreach ($languages as $language) {
                $languageCode         = $language->code();
                $languageTranslations = $language->translations();

                // merges language translations with extensions translations
                if (empty($languageTranslations) === false) {
                    $translations[$languageCode] = array_merge(
                        $translations[$languageCode] ?? [],
                        $languageTranslations,
                    );
                }
            }
        }

        return $this->translations = $this->loadTranslations($this->root('i18n:translations'), $translations);
    }

    public function loadTranslations(string $root, array $inject = []): Translations
    {
        $collection = new Translations();

        foreach (Dir::read($root) as $filename) {
            if (F::extension($filename) !== 'json' || !in_array($filename, ['de.json', 'en.json'])) {
                continue;
            }

            $locale      = F::name($filename);
            $translation = Translation::load($locale, $root . '/' . $filename, $inject[$locale] ?? []);

            $collection->data[$locale] = $translation;
        }

        return $collection;
    }

    // public function loadTranslations(string $root, array $inject = []): Translations
    // {
    //     $active_languages = [];
    //     foreach (kirby()->languages() as $lang) {
    //         array_push($active_languages, $lang . '.json');
    //     }

    //     $collection = new Translations();

    //     foreach (Dir::read($root) as $filename) {
    //         if (F::extension($filename) !== 'json' || !in_array($filename, $active_languages)) {
    //             continue;
    //         }

    //         $locale      = F::name($filename);
    //         $translation = Translation::load($locale, $root . '/' . $filename, $inject[$locale] ?? []);

    //         $collection->data[$locale] = $translation;
    //     }

    //     return $collection;
    // }
}
