<?php

namespace DisDev\Cli\Arguments;

/**
 * Un argument passé à une application Cli
 */
class Argument
{
    private string $description = '';

    private string|int|float|null $defaultValue = null;

    private bool $isRequired = false;

    private bool $noValue = false;

    private string $castTo = 'string';

    private string $prefix = '';

    private string $longPrefix = '';

    private string|int|float|bool $valueParsed = '';

    private bool $hasBeenHandled = false;

    /**
     * Créé un argument selon un nom et des options
     *
     * @param array<string, string|bool|int|float> $options
     * - description (string) Description de l'argument
     * - defaultValue (float|int|string) Valeur par défaut si l'argument n'est pas renseigné
     * - required (bool) Si l'argument est obligatoire
     * - noValue (bool) Si l'argument n'a pas besoin de valeur, il sera casté en bool
     * - prefix (string) Court prefix (-u)
     * - longPrefix (string) Long prefix (--user)
     *
     * @throws \DisDev\Cli\Arguments\Exception Si des options ne sont pas compatibles ou incorrectes
     */
    final public function __construct(protected readonly string $name, array $options = [])
    {
        if (array_key_exists('description', $options) && is_string($options['description'])) {
            $this->description = $options['description'];
        }

        if (array_key_exists('required', $options) && is_bool($options['required'])) {
            $this->isRequired = $options['required'];
        }

        if (array_key_exists('noValue', $options) && is_bool($options['noValue'])) {
            $this->noValue = $options['noValue'];
        }

        if (array_key_exists('prefix', $options) && is_string($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        if (array_key_exists('longPrefix', $options) && is_string($options['longPrefix'])) {
            $this->longPrefix = $options['longPrefix'];
        }

        if (array_key_exists('castTo', $options) && is_string($options['castTo'])) {
            $this->castTo = match ($options['castTo']) {
                'int', 'integer'  => 'integer',
                'bool', 'boolean' => throw new Exception('castTo ne peut être bool, utiliser l\'option noValue'),
                'double', 'float' => 'double',
                'string'          => 'string',
                default           => throw new Exception($options['castTo'] . ' n\'est pas un type de cast correct')
            };
        }

        if (array_key_exists('defaultValue', $options)) {
            if (!in_array(gettype($options['defaultValue']), ['double', 'integer', 'string'])) {
                throw new Exception('La valeur par défaut doit être un float, int ou string');
            }

            $this->defaultValue = $options['defaultValue'];

            // On test que le type de la valeur par défault est le même que castTo (string si non renseigné)
            if (gettype($this->defaultValue) !== $this->castTo) {
                throw new Exception(
                    'La valeur par défaut n\'est pas du même type que castTo (' . $this->castTo . ')'
                );
            }
        }

        if (($this->noValue || $this->isRequired) && $this->defaultValue) {
            throw new Exception('Un argument noValue|required ne peut avoir une valeur par défaut');
        }

        if ($this->isRequired && ($this->prefix || $this->longPrefix)) {
            throw new Exception('Un argument avec un prefix (option) ne peut être required');
        }
    }

    /**
     * Retourne la valeur de l'argument après être parsé et casté, sinon retourne la valeur par défaut
     */
    final public function getValue(): string|int|float|bool|null
    {
        if (!$this->hasBeenHandled) {
            if ($this->noValue) {
                return false;
            }

            return $this->defaultValue;
        }

        return $this->valueParsed;
    }

    /**
     * Méthode qui parse l'argument avec la valeur passé en paramètre
     *
     * @param  bool|string $value Si string, essaie de caster, si bool on check qui soit en noValue
     *
     * @throws \DisDev\Cli\Arguments\ParseException Si $value n'est pas correct selon le type de cast demandé
     */
    final public function setValueParsed(bool|string $value): void
    {
        if (is_bool($value) && $this->noValue) {
            $this->valueParsed    = $value;
            $this->hasBeenHandled = true;

            return;
        }

        if ($this->castTo === 'string') {
            $this->valueParsed    = $value;
            $this->hasBeenHandled = true;

            return;
        }

        // Throw une exception si la valeur n'est pas du bon type
        if ($this->castTo === 'integer') {
            if (!is_numeric($value)) {
                throw new ParseException("Argument {$this->name} n'est pas un nombre (doit caster en int)");
            }

            $this->valueParsed = intval($value);
        } elseif ($this->castTo === 'double') {
            if (!is_numeric($value) || strpos($value, ',') !== false) {
                throw new ParseException(
                    "Argument {$this->name} n'est pas un nombre ou contient des , (doit caster en float)"
                );
            }

            $this->valueParsed = floatval($value);
        }

        $this->hasBeenHandled = true;
    }

    /**
     * Retourne toutes les infos nécessaires de l'argument pour l'affichage
     */
    public function getInfos(): string
    {
        $toPrint = [];

        if ($prefix = $this->prefix) {
            $toPrint[] = $this->noValue ? " -$prefix" : " -$prefix={$this->name}";
        }

        if ($prefix = $this->longPrefix) {
            $toPrint[] = $this->noValue ? " --$prefix" : " --$prefix={$this->name}";
        }

        $toPrint = implode(',', $toPrint);

        if (!$this->noValue) {
            $toPrint .= ' (type: ' . $this->castTo . ')';
        }

        if ($this->defaultValue) {
            $toPrint .= ' (default: ' . $this->defaultValue . ')';
        }

        if ($this->description) {
            $toPrint .= "\n\t  " . $this->description;
        }

        return $this->name . $toPrint;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLongPrefix(): string
    {
        return $this->longPrefix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function hasNoValue(): bool
    {
        return $this->noValue;
    }

    public function getCastTo(): string
    {
        return $this->castTo;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getDefaultValue(): string|int|float|null
    {
        return $this->defaultValue;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasBeenHandled(): bool
    {
        return $this->hasBeenHandled;
    }
}
