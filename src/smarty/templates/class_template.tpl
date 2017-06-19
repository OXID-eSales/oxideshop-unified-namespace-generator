{{if $class.isInterface}}interface{{elseif $class.isAbstract}}abstract class{{else}}class{{/if}} {{$class.shortUnifiedClassName}} extends \{{$class.editionClassName}}
{

}

{{if $backwardsCompatibleClass}}
/**
 * This class alias is created for backwards compatibility only.
 * The class {{$backwardsCompatibleClass}} is deprecated since OXID eShop v6.0.0 and should not be used any more as it
 * will be removed in the future.
 * Please use {{$fullyQualifiedUnifiedClass}} instead.
 */
class_alias({{$fullyQualifiedUnifiedClass}}::class, '{{$backwardsCompatibleClass}}');
{{/if}}
