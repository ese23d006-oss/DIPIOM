@props(['courses'])

<ul class="grid-cards">
    @foreach ($courses as $course)
        <li><x-course-card :course="$course" with-progress /></li>
    @endforeach
</ul>
