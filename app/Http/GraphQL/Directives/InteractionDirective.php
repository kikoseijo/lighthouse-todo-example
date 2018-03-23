<?php

namespace App\Http\GraphQL\Directives;

use GraphQL\Language\AST\DirectiveNode;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Support\Traits\HandlesDirectives;

class InteractionDirective implements FieldResolver
{
    use HandlesDirectives;

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name()
    {
        return 'interaction';
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $value
     *
     * @return FieldValue
     */
    public function handle(FieldValue $value)
    {
        $directive = $this->fieldDirective($value->getField(), $this->name());
        $className = $this->getClassName($directive);
        $data = $this->argValue(collect($directive->arguments)->first(function ($arg) {
            return 'args' === data_get($arg, 'name.value');
        }));

        return $value->setResolver(function ($root, array $args, $context = null, $info = null) use ($className, $data) {
            // $instance = app($className);

            return $this->interaction($className, [array_merge($args, ['directive' => $data])]);

            // return call_user_func_array(
            //     [$instance, $method],
            //     [$root, array_merge($args, ['directive' => $data]), $context, $info]
            // );
        });
    }

    /**
     * Get class name for resolver.
     *
     * @param DirectiveNode $directive
     *
     * @return string
     */
    protected function getClassName(DirectiveNode $directive)
    {
        $class = $this->directiveArgValue($directive, 'class');

        if (! $class) {
            throw new DirectiveException(sprintf(
                'Directive [%s] must have a `class` argument.',
                $directive->name->value
            ));
        }

        return $class;
    }

    protected function interaction($interaction, array $parameters)
    {
        $this->call($interaction.'@validator', $parameters)->validate();

        return $this->call($interaction, $parameters);
    }

    /**
    * Will call interacion handle function if no other method its defined.
    *
    * @param  string $interaction
    * @param  array  $parameters
    * @return mixed
    */
    protected function call($interaction, array $parameters = [])
    {
        if (!str_contains($interaction, '@')) {
            $interaction = $interaction.'@handle';
        }

        list($class, $method) = explode('@', $interaction);

        $base = class_basename($class);

        return call_user_func_array([app($class), $method], $parameters);
    }


}
