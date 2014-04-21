Data Sources and Widgets
========================

One of the most powerful features of Yii is how it works with data. In Yii, you may easily output data directly in view files, which is a good approach
for the Web site's frontend. But when it comes to backend data components and widgets, Yii's ability to access data is a real timesaver.

Typically, you would take the following steps when working with one of these data components:

1. Configure the [data provider](data-providers.md). It may get its data from an array, an SQL command, an ActiveRecord query, etc.
2. Pass the data provider to a widget, such as a [list view](data-widgets.md#listview) or [grid view](data-grid.md).
3. Customize the widget to reflect the presentational style that you are after.

That's it. After doing these simple steps you will have a powerful interfaces, such as a full featured data grid that supports pagination, sorting, and
filtering, ideal for the admin part of your project.
