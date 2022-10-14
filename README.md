# Une libraie simple pour gérer des arguments parsés depuis le Cli

## **DisDev\Cli\Arguments\Argument** qui définie ce qu'est un argument
Un argument possède un nom et différentes options à passer au constructeur

Un argument ne peut que être de type `integer`, `double` ou `string` (si l'option `noValue` est passée, il sera en `bool`)

```php
new \DisDev\Cli\Arguments\Argument(string $name, array $options = [])
```

```
- description (string) Description de l'argument
- defaultValue (float|int|string) Valeur par défaut si l'argument n'est pas renseigné
- required (bool) Si l'argument est obligatoire
- noValue (bool) Si l'argument n'a pas besoin de valeur, il sera casté en bool
- prefix (string) Court prefix (-u)
- longPrefix (string) Long prefix (--user)
```

Une `DisDev\Cli\Arguments\Exception` peut être lancée à chaque instance si les options ne sont pas conformes:

- Si `castTo` est renseigné, il doit être soit `integer|int`, `double|float` ou `string`
- Si `defaultValue` est renseigné
  - le type de sa valeur doit être un `entier`, `float` ou `string`
  - le type de sa valeur doit être du même type que castTo (default `string`)
- Un argument ne peut être `required` et avoir une valeur par défaut
- Un argument ne peut être `noValue` (casté en bool) et avoir une valeur par défaut
- Un argument avec prefix et longPrefix (`-i`, `--iterator`) ne peut être `required`

## **DisDev\Cli\Arguments** est une collection d'arguments qui permet de parser des valeurs

Le constructeur permet d'ajouter un nombre indéfini d'argument

```php
new \DisDev\Cli\Arguments(\DisDev\Cli\Arguments\Argument ...$oArguments)
```

> ! L'ordre des arguments est important si un ou plusieurs arguments obligatoires sont présents, il devront être ajoutés en premier, une exception `DisDev\Cli\Arguments\Exception` sera levée sinon

### La récupération de valeurs passées se fait via la méthode `parse(string ...$arguments): void`
Associe chaque valeur passée en argument de méthode à son Argument dans la collection
- Check en premier les options (préfixés par -- ou -)
- Boucle dans les `Argument` de la collection qui sont required et associe dans l'ordre des arguments (une exception `DisDev\Cli\Arguments\ParseException` est levée si un argument required n'est pas associé)
- Les arguments restant seront associés aux derniers `Argument` de la collection (order d'ajout des `Argument`)

> ! Si un `Argument` de la collection a l'option prefix ou longPrefix (-- ou -) et qu'un argument parsé n'a pas de signe = (ex: `--test` pour un `Argument` `longPrefix => 'test'`) une `DisDev\Cli\Arguments\ParseException` sera lancée

La valeur parsée d'un argument est obtenue via `::get(string $nomArgument)`, une `DisDev\Cli\Arguments\Exception` est lancée si la collection n'a pas l'argument via son nom

Par défaut, `NULL` est retourné; `integer`, `float` ou `string` peut être retourné si l'argument a été parsé depuis `::parse()` et que l'option castTo a été set en option

- Si castTo est en `integer`, la valeur parsée doit être un chiffre numerique (conditionne avec `is_numeric()`), une `DisDev\Cli\Arguments\Exception` sera lancée sinon
- Si castTo est en `float`, la valeur parsée doit être un chiffre numerique en notation avec . (ex: 4.3, 4,3 ne sera pas autorisé et une `DisDev\Cli\Arguments\Exception` sera lancée)
- Si castTo est un `string`, la valeur parsée sera celle passée en argument

#### Implémentation
```php
$oArguments = new Arguments(new Argument('arg1'));
$oArguments->get('arg1') // NULL

$oArguments = new Arguments(new Argument('arg1', ['defaultValue' => 'test']));
$oArguments->get('arg1') // test

$oArguments = new Arguments(new Argument('arg1', ['defaultValue' => 14.3])); // Exception defaultValue doit être de type string

$oArguments = new Arguments(new Argument('arg1', ['castTo' => 'float', 'defaultValue' => 14.3])); // correct
$oArguments->get('arg1') // 14.3

// Parse d'un string
$oArguments = new Arguments(new Argument('arg1', ['required' => true]));
$oArguments->parse(); // ParseException arg1 is required
$oArguments->parse('test'); // OK $oArguments->get('arg1') = test

// Parse d'un integer
$oArguments = new Arguments(new Argument('arg1', ['required' => true, 'castTo' => 'integer']));
$oArguments->parse('test'); // Exception La valeur parsée doit être un nombre
$oArguments->parse('234'); // OK $oArguments->get('arg1') = 234

// Parse d'un float
$oArguments = new Arguments(new Argument('arg1', ['required' => true, 'castTo' => 'float']));
$oArguments->parse('12,2'); // Exception La valeur parsée doit être un nombre avec un dot en délimiteur
$oArguments->parse('12.2'); // OK $oArguments->get('arg1') = 12.2

// Parse d'argments optionnels
$oArguments = new Arguments(new Argument('arg1'), new Argument('arg2'));
$oArguments->parse('test1'); // $oArguments->get('arg1') = test1, $oArguments->get('arg1') = NULL
$oArguments->parse('test1', 'test2'); // $oArguments->get('arg1') = test1, $oArguments->get('arg1') = test2

// Parse d'arguments préfixés
$oArguments = new Arguments(new Argument('arg1', ['prefix' => 'a', 'longPrefix' => 'arg']));
$oArguments->parse('-a=testValue'); // $oArguments->get('arg1') = testValue
$oArguments->parse('-a="test Value"'); // $oArguments->get('arg1') = test Value
$oArguments->parse('--arg=testValue'); // $oArguments->get('arg1') = testValue
$oArguments->parse('--arg="test Value"'); // $oArguments->get('arg1') = test Value
```

### Plusieurs méthodes sont à disposition

- Ajoute un argument `set(Argument $oArgument): void`
- Supprime un argument `remove(string $argumentName): void`
- Retourne le nombre d'argument `count(): int`
- Affiche de manière propre les arguments requis ainsi que les arguments optionnels de la collection `printArguments(): void`
  ```
    Arguments requis:
      arg1 (type: string)
      arg2 (type: integer)

    Arguments optionnels:
      arg3 --arg3=arg3 (type: integer)
      arg4 -arg4
  ```

# Stylise et rend l'output du CLI beaucoup plus clair et propre

## **DisDev\Cli\Style** est une classe ayant uniquement des méthodes statiques qui affiche du texte

3 enum sont à disposition et utilisées par la librairie pour styliser le texte:

- `DisDev\Cli\Style\Background`: Couleurs de background
- `DisDev\Cli\Style\Foreground`: Couleurs de texte
- `DisDev\Cli\Style\Attributes`: Attributs de texte

2 méthodes principales sont à disposition pour afficher du texte stylisé:
```php
/**
 * Print le texte selon le style voulu sans passer de ligne
*/
public static function inline(string $string, Background $bg = null, Foreground $fg = null, Attribute $at = null): void;

/**
 * Print le texte selon le style voulu et passe une ligne
*/
public static function outline(string $string, Background $bg = null, Foreground $fg = null, Attribute $at = null): void;
```

1 méthode qui retourne le texte stylisé au lieu de l'afficher
```php
public static function stylize(string $string, Background $bg = null, Foreground $fg = null, Attribute $at = null): string;
```

D'autres méthodes très utiles sont à disposition également:

```php
====================================
｡◕‿◕｡ Ceci est un message titre ｡◕‿◕｡
====================================
public static function title(string $title): void;

--- Message flanké ---\n
public static function flank(string $message, string $char = '-', int $length = 3): void;

\n｡◕‿◕｡ Terminé ｡◕‿◕｡\n
public static function termine(): void;

｡◕‿◕｡ Message ｡◕‿◕｡
public static function flankStyle(string $message): void;

  (◍•﹏•) Message Warning\n
public static function warning(string $message): void; // message en jaune

  (◍•﹏•) Message Error\n
public static function error(string $message): void; // message en rouge

public static function red(string $message): void; // affiche du texte en rouge et passe une ligne
public static function green(string $message): void; // affiche du texte en vert et passe une ligne
public static function yellow(string $message): void; // affiche du texte en jaune et passe une ligne

// Affiche un texte selon la valeur booléenne (en vert si true, rouge si false) de $status et passe une ligne
public static function outlineWithBool(bool $status, string $ifTrue, string $ifFalse, string $toPrecede = ''): void;

// Affiche les détails d'une exception en rouge + sa trace en blanc si voulu
public static function exception(\Exception $e, bool $withoutTrace = false): void;
```