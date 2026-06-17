{{--
    Reusable course card component — homepage featured courses grid.

    Props:
      $image    — asset path relative to public/images/courses/
      $title    — course title string
      $desc     — short description
      $duration — e.g. "8 Weeks"
      $level    — e.g. "Beginner", "Intermediate", "Advanced"
      $slug     — (optional) course slug for detail page; falls back to apply-now
--}}
<div class="course-card">
    <div class="course-card-img">
        <img src="{{ asset('images/courses/' . $image) }}" alt="{{ $title }}" loading="lazy">
    </div>
    <div class="course-card-body">
        <div class="course-meta-tags">
            <span class="course-tag"><i class="fas fa-clock"></i> {{ $duration }}</span>
            <span class="course-tag level-{{ strtolower($level) }}">{{ $level }}</span>
        </div>
        <h3>{{ $title }}</h3>
        <p>{{ $desc }}</p>
        @if(!empty($slug))
            <a href="{{ route('courses.show', $slug) }}" class="btn btn-primary btn-sm w-100 mt-auto">
                View Course
            </a>
        @else
            <a href="{{ route('apply.now') }}" class="btn btn-primary btn-sm w-100 mt-auto">
                Apply Now
            </a>
        @endif
    </div>
</div>
