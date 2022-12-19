<?php

namespace DisDev\Cli;

use DisDev\Cli\Arguments\Argument;
use DisDev\Cli\Arguments\Exception;
use DisDev\Cli\Arguments\ParseException;

/**
 * Collection de DisDev\Cli\Arguments\Argument, qui possèdent les arguments d'une application Cli
 *
 * Possède une méthode ->parse() qui demande des valeurs string comme ceux récupérés depuis $argv
 * qui set la valeur de chaque argument et/ou throw une Exception si une erreur d'initialisation se produit
 *
 * @implements \IteratorAggregate<string, Argument>
 */
class Arguments implements \IteratorAggregate
{
    /**
     * @var array<string, Argument>
     */
    protected array $data = [];

    /**
     * Initialise la collection avec l'ajout d'arguments
     */
    public function __construct(Argument ...$oArguments)
    {
        foreach ($oArguments as $oArgument) {
            $this->data[$oArgument->getName()] = $oArgument;
        }

        $this->assertOrderRequired();
    }

    /**
     * @return \Traversable<string, Argument>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Méthode interne qui récupère l'argument et retourne l'instance ou null
     */
    private function retrieve(string $argumentName): ?Argument
    {
        return $this->data[$argumentName] ?? null;
    }

    /**
     * Ajout un argument dans la collection
     */
    public function set(Argument $oArgument): void
    {
        $this->data[$oArgument->getName()] = $oArgument;

        $this->assertOrderRequired();
    }

    /**
     * Récupère la valeur d'un argument de la collection (retourne sa valeur par défaut si non handled)
     *
     * @throws \DisDev\Cli\Arguments\Exception Si l'argument n'existe pas dans la collection
     */
    public function get(string $argumentName): string|int|float|bool|null
    {
        if (!$oArgument = $this->retrieve($argumentName)) {
            throw new Exception("L'argument $argumentName n'existe pas dans la collection");
        }

        return $oArgument->getValue();
    }

    /**
     * Supprime un argument de la collection
     */
    public function remove(string $argumentName): void
    {
        unset($this->data[$argumentName]);
    }

    /**
     * Retourne le nombre d'argument disponible dans la collection
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Méthode interne recupérant un argument de la collection selon un prefix (-)
     */
    private function getByShortPrefix(string $name): ?Argument
    {
        foreach ($this->data as $oArgument) {
            if ($oArgument->getPrefix() === $name) {
                return $oArgument;
            }
        }

        return null;
    }

    /**
     * Méthode interne recupérant un argument de la collection selon un longPrefix (--)
     */
    private function getByLongPrefix(string $name): ?Argument
    {
        foreach ($this->data as $oArgument) {
            if ($oArgument->getLongPrefix() === $name) {
                return $oArgument;
            }
        }

        return null;
    }

    /**
     * Méthode interne récupérant les arguments non traités
     */
    private function getNotHandled(): self
    {
        $oSelf = new self();

        foreach ($this->data as $oArgument) {
            if (!$oArgument->hasBeenHandled()) {
                $oSelf->set($oArgument);
            }
        }

        return $oSelf;
    }

    /**
     * Méthode interne récupérant les arguments non traités
     */
    public function getRequired(): self
    {
        if (!$this->count()) {
            return $this;
        }

        $oSelf = new self();

        foreach ($this->data as $oArgument) {
            if ($oArgument->isRequired()) {
                $oSelf->set($oArgument);
            }
        }

        return $oSelf;
    }

    /**
     * Vérifie que la collection a un ordre d'argument required respecté
     * (appelée au construct et à chaque set)
     */
    private function assertOrderRequired(): void
    {
        $argHasNotRequired = false;

        foreach ($this->data as $name => $oArgument) {
            if ($oArgument->getPrefix() || $oArgument->getLongPrefix()) {
                continue;
            }

            if (!$oArgument->isRequired()) {
                $argHasNotRequired = true;
            }

            if ($argHasNotRequired && $oArgument->isRequired()) {
                throw new Exception("Argument $name required succède d'un argument non required");
            }
        }
    }

    /**
     * Parse les arguments de la collection en checkant les arguments required,
     * avec ou sans prefix et set la valeur parsée (castée si castTo renseigné)
     *
     * @param string ...$arguments Arguments qui doivent être parsés (de type string comme la superglobal `$argv`)
     *
     * @throws \DisDev\Cli\Arguments\Exception Si un argument required n'est pas renseigné ou erreur d'argument
     */
    public function parse(string ...$arguments): void
    {
        // Si aucun argument de set, on ne check pas
        if (!$this->count()) {
            return;
        }

        // On boucle une première fois pour récupérer les prefix, on unset pour récupérer un new array sans les prefix
        foreach ($arguments as $index => $arg) {
            if (substr($arg, 0, 1) !== '-') {
                continue;
            }

            // Check de la présence d'un prefix/longPrefix
            if (strncmp($arg, '--', 2) === 0) {
                $this->setArgFromPrefix($arg, '--');
            }

            $this->setArgFromPrefix($arg, '-');

            unset($arguments[$index]);
        }

        // On boucle pour récupérer les arguments required
        foreach ($this->data as $name => $oArgument) {
            if (!$oArgument->isRequired()) {
                continue;
            }

            if (($argValue = current($arguments)) === false) {
                throw new ParseException("Argument $name is required");
            }

            $oArgument->setValueParsed($argValue);

            unset($arguments[key($arguments)]);
        }

        reset($arguments);

        // Il reste les derniers arguments non required
        $oNotHandled = $this->getNotHandled();

        if (!$oNotHandled->count() || !$arguments) {
            return;
        }

        foreach ($oNotHandled as $oArgument) {
            $arg = current($arguments);

            // On skip les arguments préfixés, déjà handled dans la première boucle
            if ($oArgument->getLongPrefix() || $oArgument->getPrefix()) {
                continue;
            }

            $oArgument->setValueParsed($arg);

            if (!next($arguments)) {
                return;
            }
        }
    }

    /**
     * Affiche les arguments requis et/ou ceux optionnels dans un affichage propre
     */
    public function printArguments(): void
    {
        $oSelf = clone $this;
        $oArgs = $oSelf->getRequired();

        // On print les arguments obligatoires
        if ($oArgs->count()) {
            print "Arguments requis:";

            foreach ($oArgs as $name => $oArg) {
                print "\n\t" . $oArg->getInfos();

                $oSelf->remove($name);
            }
        }

        if (!$oSelf->count()) {
            return;
        }

        print "\n\nArguments optionnels:";

        /**
         * On affiche les arguments restants
         *
         * @var Argument $oArg
         */
        foreach ($oSelf as $name => $oArg) {
            print "\n\t" . $oArg->getInfos();
        }
    }

    /**
     * Méthode permettant de set la valeur parsed de l'argument commençant par - (une option)
     */
    private function setArgFromPrefix(string $arg, string $prefix): void
    {
        $countPrefix = strlen($prefix);

        // On enlève les quotes et les deux premiers --
        $arg = mb_substr(str_replace(['\'', '"'], '', $arg), $countPrefix);

        // On récupère le nom de l'argument (si un egal est trouvé, on prend partie avant l'egal, sinon l'arg complet)
        $argName = $arg;
        if (($equalPosition = mb_strpos($arg, '=')) !== false) {
            $argName = mb_substr($arg, 0, $equalPosition);
        }

        // On retrouve l'argument depuis soit le shortPrefix soit le longPrefix
        $oArgument = $countPrefix === 1 ? $this->getByShortPrefix($argName) : $this->getByLongPrefix($argName);

        if (!$oArgument) {
            return;
        }

        // On check qu'on a pas un argument noValue
        if ($oArgument->hasNoValue()) {
            $oArgument->setValueParsed(true);

            return;
        }

        // Si un égal n'a pas été trouvé et que l'argument requiert une valeur (noValue => false)
        if ($equalPosition === false) {
            throw new ParseException("Argument avec valeur commençant par $prefix ($arg) n'a pas de signe =");
        }

        $argValue = mb_substr($arg, $equalPosition + 1);

        $oArgument->setValueParsed($argValue);
    }
}
