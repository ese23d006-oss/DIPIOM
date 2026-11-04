@extends('layouts.app')

@section('title', 'Системийн тухай')

@section('content')
    <x-page-header title="Системийн тухай"
                   lead="Энэ систем нь харааны бэрхшээлтэй иргэд онлайн сургалтад бие даан хамрагдах боломжийг бүрдүүлэх зорилготой." />

    <div class="card-surface card-pad">
        <h2>Хэрхэн ашиглах вэ</h2>
        <ul class="mb-3">
            <li>Хуудас бүрийн эхэнд байгаа холбоосоор үндсэн агуулга руу шууд шилжинэ.</li>
            <li>Tab болон Shift + Tab товчоор холбоос, товчлуурын хооронд шилжинэ.</li>
            <li>Enter эсвэл Space товчоор товчлуурыг ажиллуулна.</li>
            <li>Escape товчоор нээлттэй цэс болон цонхыг хаана.</li>
        </ul>
        <a class="btn btn-primary" href="{{ route('courses.index') }}">Сургалтуудыг үзэх</a>
    </div>
@endsection
