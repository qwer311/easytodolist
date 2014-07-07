<!DOCTYPE HTML> <!-- Latest compiled and minified CSS -->
<head>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
    {{ HTML::style('static/css/style.css') }}
    {{ HTML::style('static/css/datepicker.css') }}
    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <meta charset="UTF-8">
    <style>
    .datepicker {
    line-height: 20px;
    }
    .datepicker .table-condensed th,
    .datepicker .table-condensed td {
    padding: 4px 5px;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="col-md-6">
            <section class="panel">
                <header class="panel-heading">
                    やる事リスト
                </header>
                <div class="panel-body">
                    <ul class="to-do-list" id="sortable-todo">
                    </ul>
                </div>
                <div  id="todoapp">
                    <div class="todo-action-bar">
                        <div class="row" style="margin-left:10px">
                            <div class="col-xs-4 todo-search-wrap" >
                                <input type="text" id="new-todo" class="form-control  pull-right"  placeholder=" ">
                            </div>
                            <div class="col-xs-4 input-group date" id="dp3" data-date="" data-date-format="yyyy-mm-dd" style="float:left;width:25%;margin-left:2px">
                                <input class="form-control due_date_add"  size="16" type="text" value="">
                                <span class="input-group-addon"><span  class="add-on glyphicon glyphicon-calendar"></span></span>
                            </div>
                            <div class="col-xs-4 btn-add-task">
                                <button id="addtask_new" class="btn btn-primary">
                                追加 </button><i class="fa fa-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
            </section>
        </div>
    </div>
</body>
<script type="text/template" id="item-template">


<li class="clearfix">
    <span class="drag-marker">
    <i></i>
    </span>
    <div class="todo-check pull-left"  >
        <input  type="checkbox" >
        <label class="toggle"></label>
    </div>
    
    <p class="todo-title">
    <%- title %>
    </p>
    <div class="todo-actionlist pull-right clearfix">
        <i class="glyphicon glyphicon-align-justify"></i>
    </div>
</li>

<div  class="item-detail<%- order %>"  style="margin-bottom:5px;display:none " >
    <input   class="form-control title" size="16" type="text" value="<%- title %>" style="margin-bottom:5px">
    <textarea  class="details description" style="width:100%;height:70px" ><%- description %></textarea>
    <div class="col-xs-4 input-group date" id="dp3" data-date="" data-date-format="yyyy-mm-dd" style="float:left;width:50%;margin-top:5px">
        <input class="form-control due_date"  size="16" type="text" value="<%- due_date %>">
        <span class="input-group-addon"><span  class="add-on glyphicon glyphicon-calendar"></span></span>
    </div>
    <span class="glyphicon glyphicon-ok" ></span>
    <button  id="save" class="btn btn-default" style="width:30%;float:right;margin-top:5px">保存</button>
    <div style="clear:both" ></div>
</div>
</script>
<script type="text/template" id="stats-template">
</script>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
{{ HTML::script('static/js/json2.js') }}
{{ HTML::script('static/js/jquery.min.js') }}
{{ HTML::script('static/js/underscore.min.js') }}
{{ HTML::script('static/js/backbone.min.js') }}
{{ HTML::script('static/js/jquery.slimscroll.min.js') }}
{{ HTML::script('static/js/bootstrap-datepicker.js') }}
<script>
var app = app || {};
// Todo Model

app.Todo = Backbone.Model.extend({
 
    defaults: {
        title: '',
        title_org: '',
        description: '',
        due_date: '',
        month: 0,
        order: 0,
        completed: "no"
    },

    toggle: function() {
        this.save({
            completed: ((this.get('completed') == "yes") ? "no" : "yes")
        });
    },
    validate: function(attrs) {
        if (attrs.title == undefined) {
            return "Title can't be empty";
        }
    }
});

// Todo Collection
var TodoList = Backbone.Collection.extend({
    url: "./api/v1/todos",
    // Reference to this collection's model.
    model: app.Todo,

    completed: function() {
        return this.filter(function(todo) {
            if (todo.get('completed') == "yes") {
                return true;
            } else {
                return false;
            }
        });
    },

    nextOrder: function() {
        if (!this.length) {
            return 1;
        }
        return this.last().get('order') + 1;
    },
    // Todos are sorted by their original insertion order.
    comparator: function(todo) {
        return todo.get('order');
    }
});

// Create our global collection of **Todos**.
app.Todos = new TodoList();
// Todo Item View
// --------------
// The DOM element for a todo item...
app.TodoView = Backbone.View.extend({

    tagName: 'li',
    // Cache the template function for a single item.
    template: _.template($('#item-template').html()),
    // The DOM events specific to an item.
    events: {
        'click #save': 'saveItem',
        'click .todo-actionlist': 'ItemControl',
        'click .toggle': 'togglecompleted'

    },

    initialize: function() {

    },

    render: function() {

        this.$el.html(this.template(this.model.toJSON()));
        this.$el.toggleClass('completed', (this.model.get('completed') == "yes" ? true : false));

        this.title = this.$('.title');
        this.description = this.$('.description');

        this.due_date = this.$('.due_date');
        this.order = this.model.get('order');
        this.title_org = this.model.get('title_org');

        return this;
    },

    ItemControl: function() {
        var order = this.order;
        $('.item-detail' + order).slideToggle("slow");
    },


    // Toggle the `"completed"` state of the model.
    togglecompleted: function() {
        this.$el.toggleClass('completed', (this.model.get('completed') == "no" ? true : false));
        if (this.model.get('completed') == "no") {
            this.$el.find("input[type=checkbox]").attr("checked", true);
        } else {
            this.$el.find("input[type=checkbox]").attr("checked", false);
        }
        this.model.toggle();
    },

    addOne: function(todo) {
        var view = new app.TodoView({
            model: todo
        });
        $('#sortable-todo').append(view.render().el);
    },
    addAll: function() {
        //this.$('#sortable-todo').html('');
        $('#sortable-todo').html('');
        var categorised = app.Todos.groupBy(function(todo) {
            return todo.get("month");
        });
        for (month in categorised) {
            title_html = '<li class="clearfix"><div class="todo-check pull-left"></div><p class="todo-title">' + month + '月</p></li>';
            $('#sortable-todo').append(title_html);
            _.each(categorised[month], this.addOne);
        };
    },
    saveItem: function() {
        this.$el.find('.glyphicon-ok').css("visibility", "visible");

        var description = this.description.val().trim();
        var due_date = this.due_date.val().trim();
        var order = this.order;

        var month = this.due_date.val().substr(5, 2);
        var date = this.due_date.val().substr(8, 2);
        var order = parseInt(month + date);
        var title_head = month + '/' + date + ':';
        var title = title_head + this.title_org;
        this.model.save({
            title: title,
            description: description,
            due_date: due_date,
            month: parseInt(month),
            order: order
        });
        alert("保存しました。");
        app.Todos.sort();
        this.addAll();
    },

    close: function() {
        var value = this.input.val().trim();
        if (value) {
            this.model.save({
                title: value
            });
        } else {
            this.clear();
        }
        this.$el.removeClass('editing');
    }
});

app.AppView = Backbone.View.extend({
  
    el: '#todoapp',

    statsTemplate: _.template($('#stats-template').html()),

    events: {
        'click #addtask_new': 'createOnEnter'

    },

    initialize: function() {
        this.input = this.$('#new-todo');
        this.allCheckbox = this.$('#toggle-all')[0];
        this.$footer = this.$('#footer');
        this.$main = this.$('#main');
        app.Todos.on('add', this.addOne, this);
        app.Todos.on('reset', this.addAll, this);
        app.Todos.on('change:completed', this.filterOne, this);
        app.Todos.on('filter', this.filterAll, this);
        app.Todos.on('all', this.render, this);
        app.Todos.fetch();
    },


    addOne: function(todo) {
        var view = new app.TodoView({
            model: todo
        });
        $('#sortable-todo').append(view.render().el);
    },

    addAll: function() {

        $('#sortable-todo').html('');
        var categorised = app.Todos.groupBy(function(todo) {
            return todo.get("month");
        });
        for (month in categorised) {
            title_html = '<li class="clearfix"><div class="todo-check pull-left"></div><p class="todo-title">' + month + '月</p></li>';
            $('#sortable-todo').append(title_html);
            _.each(categorised[month], this.addOne);
        };
    },
  

    newAttributes: function() {
        var due_date = $('.due_date_add').val();
        var month = due_date.substr(5, 2);

        var date = due_date.substr(8, 2);
        var title_head = month + '/' + date + ':';
        var order = parseInt(month + date);
        return {
            title_org: this.input.val().trim(),
            title: title_head + this.input.val().trim(),
            order: order,
            due_date: due_date,
            month: parseInt(month),
            completed: "no"
        };
    },

    createOnEnter: function(e) {

        if (!this.input.val().trim()) {
            return;
        }

        app.Todos.create(this.newAttributes());
        this.input.val('');

        this.addAll();

    }
});

$(function() {
    new app.AppView();
}); </script>
    <script>
    $( document ).ready(function() {
    $('.date').datepicker();
    });
    $('.glyphicon-calendar').live('click', function() {
    $('.date').datepicker();
    });
    $('.panel-body').slimScroll({
    height: '300px'
    });
    </script >
</html>