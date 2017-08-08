Allows to filter any list's results

To create a new filter you have to build a Filter class extending the abstract Victoire\FilterBundle\Filter\BaseFilter class

    <?php

        namespace AppBundle\Filter;

        use Victoire\FilterBundle\Filter\BaseFilter;

        class TagFilter extends BaseFilter {

            /* will be the entry point to generate the result query of a filter */
            public function buildQuery(){}

            /* BaseFilter is an extension of symfony AbstractType so you can generate a form as usual */
            public function buildForm(){}

            /* Mandatory since when a filter is apply the WidgetListingContentResolver will identify the good filter with this method */
            public function getName(){}

            /* Method used in the WidgetListingContentResolver to recover the selected entity */
            public function getFilters(){}
        }

  And you have to declare it in your services

        victoire_blog.tag_filters.form.type:
            class: AppBundle\Filter\TagFilter
            parent: victoire_filter_bundle.abstract_base_filter
            tags:
                - { name: form.type }
                - { name: victoire_core.filter }

If your list has been created in query mod you can use the FilterFormFieldQueryHandler service
wich is accessible in the Base Filter to build your query.

    /* he take the WidgetFilter and the class name of the filtred entity and return an array of that entities */
    $this->filterQueryHandler->handle($options['widget'], Tag::class);