<?php

namespace Charlyp\AutocompleteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Reusable Autocomplete FormType
 *
 * @requires Charlyp\AutocompleteBundle\Resources\public\js\autocomplete-manager.js
 *
 * @author Charles Pourcel <ch.pourcel@gmail.com>
 */
class AutocompleteType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', 'text', array(
                'property_path' => $options['property_path'],
                //Text field value is mapped only if mapped is set and true and property_path is set on the parent
                //FormType for this field
                'mapped' => ($options['mapped'] && !empty($options['property_path'])),
                'attr' => array_merge(
                    array(
                        'class' => $options['class'],
                        //The data-toggle attribute is required to trigger all the javascript autocomplete behaviour
                        'data-toggle' => 'autocomplete',
                    ),
                    $options['attr'] //Merge the attributes passed by the parent FormType to the text field attributes
                )
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        //Merge all the route parameters together
        $routeParameters = array_merge($options['data_route_parameters'], array($options['data_route_param_name'] => '__' . $options['data_route_param_name'] . '__'));

        //Build the search URL from the provided route name and the corresponding parameters
        $view->children['text']->vars['attr']['data-url'] = $this->router->generate(
            $options['data_route_name'],
            $routeParameters
        );

        //Only if a target is set
        if (array_key_exists('data_target', $options)) {
            $targetFieldId = null;
            //Look first for the target in the current formType
            if ($view->offsetExists($options['data_target'])) {
                $targetFieldId = $view->children[$options['data_target']]->vars['id'];
            } else {
                //If not found look for it in the parent
                $targetFieldId = $view->parent->children[$options['data_target']]->vars['id'];
            }
            //Retrieve the id of the target form field to be able to update its value using javascript later on
            $view->children['text']->vars['attr']['data-target'] = $targetFieldId;

            $targetValueKeyName = null;
            if ($options['data_target_value_key']) {
                $targetValueKeyName =  $options['data_target_value_key'];
            } else {
                //If target value key is not set, defaults to target name
                $targetValueKeyName = $options['data_target'];
            }
            //Set the name of the key in the selected JSON object whose value should be put in the target field value
            $view->children['text']->vars['attr']['data-target-value-key'] = $targetValueKeyName;
        }

        //Set the name of the key in the JSON object whose value should be set as the text field value
        $view->children['text']->vars['attr']['data-value-key'] = $options['data_field_value_key'];

        //Set the name of the key in the JSON object whose value should be considered as option label
        if (array_key_exists('data_option_label_key', $options)) {
            $view->children['text']->vars['attr']['data-option-label-key'] = $options['data_option_label_key'];
        } else {
            $view->children['text']->vars['attr']['data-option-label-key'] = $view->vars['name'];
        }

        //Propagate the name of the search query key to the autocomplete javascript
        $view->children['text']->vars['attr']['data-url-param-name'] = $options['data_route_param_name'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class'                 => 'autocomplete', //Default class applied to the autocomplete field
            'data_field_value_key'  => 'value',
            'data_route_param_name' => 'search',
            'data_route_parameters' => array(),
            'inherit_data'          => true,
        ));
        $resolver->setRequired(array(
            'data_route_name', //Name of the route used to search for elements (MUST return JSON response)
            'data_route_param_name', //Name of the query param containing the searched value
        ));
        $resolver->setOptional(array(
            'property_path', //Name of the Entity property whose value should be updated by the autocomplete text field value
            'class',
            'data_target', //Name of the FormType field whose value should be updated by the javascript autocomplete process
            'data_target_value_key', //Name of the key in the selected JSON object whose value should be put in the data_target field
            'data_route_parameters', //Array of additional route parameters which should be added to the search route
            'data_option_label_key', //Name of the key in the JSON object whose value should be considered as option label
        ));
        $resolver->setAllowedTypes(array(
            'data_target'           => 'string',
            'data_route_name'       => 'string',
            'data_route_param_name' => 'string',
            'data_target_value_key' => 'string',
            'data_route_parameters' => 'array',
            'data_option_label_key' => 'string',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'autocomplete';
    }
}
