---
title: Projects
---

{% for project in site.projects %}
<h2><a href="/{{ project.name }}">{{ project.title }}</a> &nbsp; &nbsp; <span style="font-size:70%;color:gray">{{ project.lang }}</span></h2>
{{ project.description }}<br>
<a href="/{{ project.name }}/latest">{{ project.version }}</a> &middot;
<a href="/{{ project.name }}/CHANGELOG">changelog</a> &middot;
<a href="/{{ project.name }}/docs">docs</a> &middot;
<a href="https://github.com/hiqdev/{{ project.package | default: project.name }}">github</a> &middot;
<a href="/packages">{{ project.packages_num }} packages</a>

{% endfor %}