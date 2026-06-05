<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - HRC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-slate-700">HRC LOGIN</h2>

        <!-- ส่วนแสดงข้อความ Error เมื่อ Login ไม่สำเร็จ -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-1">User ID</label>
                <input type="text" name="username" value="{{ old('username') }}" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror" 
                       required autofocus>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-1">Password</label>
                <input type="password" name="password" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror" 
                       required>
            </div>

            <button type="submit" class="w-full bg-blue-800 text-white py-2 rounded-lg hover:bg-blue-900 transition font-bold">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>

</body>
</html>