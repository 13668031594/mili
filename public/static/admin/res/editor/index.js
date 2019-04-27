
function createEditor() {
    return new Simditor({
        toolbar: [
            'title', 'bold', 'italic', 'underline', 'strikethrough', 'fontScale',
            'color', '|', 'ol', 'ul', 'blockquote', 'code', 'table', '|', 'link',
             'hr', '|', 'alignment'
        ],
        textarea: "#editor",
        placeholder: '写点什么...',
//        defaultImage: '/static/home/images/logo.png',
//         'image',
        imageButton: ['upload'],
        upload: {
            url: '/upload.php',
            fileKey: 'file',
            leaveConfirm: '正在上传文件..',
            connectionCount: 3
        }
    });
}
