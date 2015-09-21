/*global blogCategories, $ */


function BlogCategory(blogCategoryElement)
{
    this.element = $vic(blogCategoryElement);
    if ($vic(blogCategoryElement).data('index')) {
        this.index = $vic(blogCategoryElement).data('index');
    } else {
        this.index = $vic(blogCategoryElement).children('[data-init="true"]').length;
    }

    var lastMaxId = 0;
    $vic('[data-init=true]').each(function(index, el) {

        if (!isNaN($vic(el).attr('data-blog-category'))
            && $vic(el).attr('data-blog-category') > lastMaxId) {
            lastMaxId = parseInt($vic(el).attr('data-blog-category'));
        }
    });
    this.id = lastMaxId + 1;

    this.parentId =  $vic(blogCategoryElement).parents('li[role="blogCategory"]').first().data('blog-category');
    //get the parent by its id
    if (this.parentId == null || this.parentId == 0) {
        this.parent = null;
        this.parentId = 0;
    } else {
        this.parent = blogCategories[this.parentId];
    }
    blogCategories[this.id] = this;


}

function addBlogCategoryRootItem(el)
{
    var blogCategoryElement = $vic(el).parents('div').first().prev('ul');
    // var parentBlogCategory = $vic('#blogCategory-children');
    var blogCategory = new BlogCategory(blogCategoryElement);
    blogCategory.init();
    blogCategory.append();
}
function addBlogCategoryRow(el)
{
    var blogCategoryElement = $vic(el).parents('span.add_blogCategory_link-container').first().next('ul');
    // var parentBlogCategory = $vic(el).parents('[role="blogCategory-item"]').first();
    var blogCategory = new BlogCategory(blogCategoryElement);
    blogCategory.init();
    blogCategory.append();
}

function deleteBlogCategoryRow(el)
{
    var blogCategory = $vic(el).parents('li[role="blogCategory"]').first();
    blogCategories[blogCategory.data('blog-category')] = undefined;
    blogCategory.remove();

}

function initBlogCategories()
{
    var links = $vic('.add_blogCategory_link');
    var blogCategory = {id: 0};

    //we want the links from the bottom to the top
    $vic.each(links, function (index, link) {

        var blogCategoryElement = $vic(link).parents('li[role="blogCategory"]').first();
        if (blogCategoryElement.length > 0) {
            blogCategory = new BlogCategory(blogCategoryElement);
            blogCategory.update();
        }
    });

    //This is exactly the same loop as the one just before
    //We need to close the previous loop and iterate on a new one because
    //we operated on the DOM that is updated only when the loop ends.
    $vic.each(links, function (index, link) {
        var blogCategoryElement = $vic(link).parents('li[role="blogCategory"]').first();
        var blogCategory = blogCategories[blogCategoryElement.attr('data-blog-category')];

        var parentBlogCategoryElement = $vic(blogCategoryElement).parents('li[role="blogCategory"]').first();

        var parentBlogCategory = blogCategories[parentBlogCategoryElement.attr('data-blog-category')];
        if (parentBlogCategory != undefined) {
            blogCategory.parentId = parentBlogCategory.id;
            blogCategory.parent = parentBlogCategory;

            blogCategories[blogCategory.id] = blogCategory;
        }
    });
}

BlogCategory.prototype.init = function ()
{
    var currentBlogCategory = this;
    var name = '[' + currentBlogCategory.index + ']';
    var i = 0;
    do {
        i++;
        if (currentBlogCategory.parent != null) {
            name = '[' + currentBlogCategory.parent.index + '][children]' + name;
        }
        currentBlogCategory = currentBlogCategory.parent;
    } while (currentBlogCategory != null && i < 10);
    var newForm = prototype.replace(/\[__name__\]/g, name);
    var name =  name.replace(/\]\[/g, '_');
    var name =  name.replace(/\]/g, '_');
    var name =  name.replace(/\[/g, '_');
    var newForm = newForm.replace(/__name__/g, name);
    var newForm = newForm.replace(/__blogCategory_id__/g, this.id);
    var newForm = newForm.replace(/__blogCategory_index__/g, this.index);
    this.newForm = $vic.parseHTML(newForm);
    $vic(this.newForm).attr('data-init', "true");
};

BlogCategory.prototype.update = function ()
{
    $vic(this.element).replaceWith(this.element);
    $vic(this.element).attr('data-blog-category', this.id);
    $vic(this.element).attr('data-init', "true");
};
BlogCategory.prototype.append = function ()
{
    $vic('[data-blog-category="' + this.parentId + '"]').children('[role="blogCategory-container"]').first().append(this.newForm);
};
$vic(document).on('submit', '#victoire_blog_settings_form', function(event) {
    event.preventDefault();
    var form = $vic(this);
    var formData = $vic(form).serializeArray();
    formData = $vic.param(formData);
    if ($vic(form).attr('enctype') == 'multipart/form-data') {
        var formData = new FormData($vic(form)[0]);
        var contentType = false;
    }

    $vic.ajax({
        url         : $vic(form).attr('action'),
        context     : document.body,
        data        : formData,
        type        : $vic(form).attr('method'),
        contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
        processData : false,
        async       : true,
        cache       : false,
        success     : function(jsonResponse) {
            if (jsonResponse.hasOwnProperty("url")) {
                congrat(jsonResponse.message, 10000);
                window.location.replace(jsonResponse.url);
            }else{
                $vic('#victoire-blog-settings').html(jsonResponse.html);
                warn(jsonResponse.message, 10000);
            }
        }
    });
});
$vic(document).on('submit', '#victoire_blog_category_form', function(event) {
    event.preventDefault();
    var form = $vic(this);
    var formData = $vic(form).serializeArray();
    formData = $vic.param(formData);
    if ($vic(form).attr('enctype') == 'multipart/form-data') {
        var formData = new FormData($vic(form)[0]);
        var contentType = false;
    }

    $vic.ajax({
        url         : $vic(form).attr('action'),
        context     : document.body,
        data        : formData,
        type        : $vic(form).attr('method'),
        contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
        processData : false,
        async       : true,
        cache       : false,
        success     : function(jsonResponse) {
            if (jsonResponse.hasOwnProperty("url")) {
                congrat(jsonResponse.message, 10000);
                window.location.replace(jsonResponse.url);
            }else{
                $vic('#victoire-blog-category').html(jsonResponse.html);
                warn(jsonResponse.message, 10000);
            }
        }
    });
});

