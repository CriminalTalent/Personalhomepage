<?php
// avocado-edition/bulk_upload.php
// 아보카도 에디션 일괄 업로드 시스템

if (!defined('_GNUBOARD_')) {
    include_once('./common.php');
}

// 권한 체크
if (!$is_member) {
    alert('회원만 이용할 수 있습니다.');
}

$bo_table = $_GET['bo_table'] ?? 'portfolio';
$board = get_board_db($bo_table);

if (!$board) {
    alert('존재하지 않는 게시판입니다.');
}

// 게시판 쓰기 권한 체크
$write_min = (int)$board['bo_write_min'];
if ($member['mb_level'] < $write_min) {
    alert('글쓰기 권한이 없습니다.');
}

$g5['title'] = '일괄 업로드 - '.$board['bo_subject'];
include_once(G5_PATH.'/head.php');

// AJAX 요청 처리
if ($_POST['action'] === 'bulk_upload') {
    header('Content-Type: application/json');
    
    $category = $_POST['category'] ?? '';
    $author = $_POST['author'] ?? $member['mb_name'];
    $start_number = (int)($_POST['start_number'] ?? 1);
    $title_format = $_POST['title_format'] ?? 'filename';
    
    $results = [];
    $success_count = 0;
    $error_count = 0;
    
    if (!empty($_FILES['files']['name'])) {
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $result = processSingleUpload($i, $bo_table, $category, $author, $start_number, $title_format);
                $results[] = $result;
                
                if ($result['success']) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($results),
        'success_count' => $success_count,
        'error_count' => $error_count,
        'results' => $results
    ]);
    exit;
}

// 개별 파일 업로드 처리 함수
function processSingleUpload($index, $bo_table, $category, $author, $start_number, $title_format) {
    global $member, $board, $config;
    
    $file_name = $_FILES['files']['name'][$index];
    $file_tmp = $_FILES['files']['tmp_name'][$index];
    $file_size = $_FILES['files']['size'][$index];
    $custom_title = $_POST['custom_titles'][$index] ?? '';
    
    // 파일 확장자 체크
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return [
            'success' => false,
            'filename' => $file_name,
            'error' => '지원하지 않는 파일 형식입니다.'
        ];
    }
    
    // 파일 크기 체크 (5MB)
    if ($file_size > 5 * 1024 * 1024) {
        return [
            'success' => false,
            'filename' => $file_name,
            'error' => '파일 크기가 5MB를 초과합니다.'
        ];
    }
    
    try {
        // 제목 생성
        $base_title = pathinfo($file_name, PATHINFO_FILENAME);
        $episode_number = $start_number + $index;
        
        switch ($title_format) {
            case 'filename':
                $post_title = $custom_title ?: $base_title;
                break;
            case 'number':
                $post_title = sprintf('%03d. %s', $episode_number, $custom_title ?: $base_title);
                break;
            case 'custom':
                $post_title = $custom_title ?: $base_title;
                break;
            default:
                $post_title = $base_title;
        }
        
        // 게시글 데이터 준비
        $wr_id = get_next_num($bo_table);
        $wr_num = get_next_num($bo_table);
        
        $sql_data = [
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'wr_num' => $wr_num,
            'wr_reply' => '',
            'wr_comment' => 0,
            'ca_name' => $category,
            'wr_option' => 'html1',
            'wr_subject' => addslashes($post_title),
            'wr_content' => addslashes("<!-- 일괄 업로드로 생성된 게시글 -->\n\n" . $post_title),
            'wr_link1' => '',
            'wr_link2' => '',
            'wr_name' => addslashes($author),
            'wr_password' => '',
            'wr_email' => $member['mb_email'],
            'wr_homepage' => $member['mb_homepage'],
            'wr_datetime' => G5_TIME_YMDHIS,
            'wr_ip' => $_SERVER['REMOTE_ADDR'],
            'wr_1' => $episode_number, // 회차 번호 저장
            'wr_2' => '',
            'wr_3' => '',
            'wr_4' => '',
            'wr_5' => '',
            'wr_6' => '',
            'wr_7' => '',
            'wr_8' => '',
            'wr_9' => '',
            'wr_10' => '',
            'mb_id' => $member['mb_id']
        ];
        
        // 게시글 삽입
        $sql = "INSERT INTO {$g5['write_prefix']}{$bo_table} SET ";
        $sql_parts = [];
        foreach ($sql_data as $key => $value) {
            $sql_parts[] = "`{$key}` = '{$value}'";
        }
        $sql .= implode(', ', $sql_parts);
        
        $result = sql_query($sql);
        
        if (!$result) {
            throw new Exception('게시글 등록에 실패했습니다.');
        }
        
        // 파일 업로드 처리
        $upload_result = uploadFileForPost($bo_table, $wr_id, $file_tmp, $file_name, $index);
        
        if (!$upload_result['success']) {
            // 게시글 삭제 (롤백)
            sql_query("DELETE FROM {$g5['write_prefix']}{$bo_table} WHERE wr_id = '{$wr_id}'");
            throw new Exception($upload_result['error']);
        }
        
        // 게시판 정보 업데이트
        sql_query("UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}'");
        
        return [
            'success' => true,
            'filename' => $file_name,
            'title' => $post_title,
            'wr_id' => $wr_id,
            'episode_number' => $episode_number
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'filename' => $file_name,
            'error' => $e->getMessage()
        ];
    }
}

// 파일 업로드 함수
function uploadFileForPost($bo_table, $wr_id, $tmp_file, $original_name, $file_index) {
    global $g5;
    
    // 업로드 디렉토리 생성
    $data_dir = G5_DATA_PATH.'/file/'.$bo_table;
    if (!is_dir($data_dir)) {
        @mkdir($data_dir, G5_DIR_PERMISSION, true);
        @chmod($data_dir, G5_DIR_PERMISSION);
    }
    
    // 파일명 생성 (중복 방지)
    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $new_filename = date('YmdHis').'_'.mt_rand(1000, 9999).'.'.$file_extension;
    $upload_path = $data_dir.'/'.$new_filename;
    
    // 파일 이동
    if (!move_uploaded_file($tmp_file, $upload_path)) {
        return [
            'success' => false,
            'error' => '파일 업로드에 실패했습니다.'
        ];
    }
    
    @chmod($upload_path, G5_FILE_PERMISSION);
    
    // 파일 정보 DB 저장
    $file_size = filesize($upload_path);
    $sql = "INSERT INTO {$g5['board_file_table']} SET
            bo_table = '{$bo_table}',
            wr_id = '{$wr_id}',
            bf_no = '0',
            bf_source = '".addslashes($original_name)."',
            bf_file = '{$new_filename}',
            bf_download = 0,
            bf_content = '',
            bf_filesize = '{$file_size}',
            bf_width = 0,
            bf_height = 0,
            bf_type = 1,
            bf_datetime = '".G5_TIME_YMDHIS."'";
    
    $result = sql_query($sql);
    
    if (!$result) {
        @unlink($upload_path);
        return [
            'success' => false,
            'error' => '파일 정보 저장에 실패했습니다.'
        ];
    }
    
    return [
        'success' => true,
        'filename' => $new_filename,
        'original_name' => $original_name
    ];
}

function get_next_num($bo_table) {
    global $g5;
    $row = sql_fetch("SELECT MAX(wr_id) as max_id FROM {$g5['write_prefix']}{$bo_table}");
    return ($row['max_id'] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $g5['title'] ?></title>
    <style>
        /* 여기에 이전 CSS 코드 삽입 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Malgun Gothic', sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .breadcrumb {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }

        .upload-zone {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 60px 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafafa;
            margin-bottom: 30px;
        }

        .upload-zone:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .upload-zone.dragover {
            border-color: #007bff;
            background: #e6f3ff;
            transform: scale(1.02);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="<?php echo G5_URL ?>">홈</a> > 
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>"><?php echo $board['bo_subject'] ?></a> > 
                일괄 업로드
            </div>
            <h1 class="page-title">📁 일괄 업로드</h1>
            <p>여러 이미지를 한번에 업로드하여 각각 개별 게시글로 생성합니다.</p>
        </div>

        <form id="bulkUploadForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="bulk_upload">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            
            <!-- 기본 설정 -->
            <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 20px;">📝 기본 설정</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label">카테고리 (시리즈명)</label>
                        <input type="text" name="category" class="form-input" placeholder="예: 내 웹툰 제목" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">작가명</label>
                        <input type="text" name="author" class="form-input" value="<?php echo $member['mb_name'] ?>">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">시작 회차 번호</label>
                        <input type="number" name="start_number" class="form-input" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">제목 형식</label>
                        <select name="title_format" class="form-select">
                            <option value="filename">파일명 그대로</option>
                            <option value="number">회차 번호 + 파일명</option>
                            <option value="custom">사용자 정의</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 파일 업로드 영역 -->
            <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div class="upload-zone" id="uploadZone">
                    <div style="font-size: 48px; margin-bottom: 20px;">📁</div>
                    <div style="font-size: 18px; margin-bottom: 10px;">이미지를 드래그하여 업로드하세요</div>
                    <div style="color: #666; font-size: 14px; margin-bottom: 20px;">
                        또는 클릭하여 파일을 선택하세요<br>
                        <small>지원 형식: JPG, PNG, GIF, WEBP (최대 5MB)</small>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                        파일 선택
                    </button>
                    <input type="file" id="fileInput" name="files[]" multiple accept="image/*" style="display: none;">
                </div>
            </div>

            <!-- 미리보기 영역 -->
            <div id="previewSection" style="display: none; background: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 20px;">📋 업로드 미리보기</h3>
                <div id="previewGrid"></div>
            </div>

            <!-- 액션 버튼 -->
            <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center;">
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>" class="btn btn-secondary">취소</a>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">업로드 시작</button>
            </div>
        </form>

        <!-- 결과 표시 영역 -->
        <div id="resultSection" style="display: none;"></div>
    </div>

    <script>
        let selectedFiles = [];

        // 드래그 앤 드롭 및 파일 선택 이벤트
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');

        uploadZone.addEventListener('click', () => fileInput.click());
        
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            selectedFiles = Array.from(files);
            updatePreview();
        }

        function updatePreview() {
            const previewSection = document.getElementById('previewSection');
            const previewGrid = document.getElementById('previewGrid');
            const submitBtn = document.getElementById('submitBtn');

            if (selectedFiles.length === 0) {
                previewSection.style.display = 'none';
                submitBtn.style.display = 'none';
                return;
            }

            previewSection.style.display = 'block';
            submitBtn.style.display = 'inline-block';

            previewGrid.innerHTML = `<p>선택된 파일: ${selectedFiles.length}개</p>`;
            selectedFiles.forEach((file, index) => {
                const div = document.createElement('div');
                div.style.cssText = 'border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 8px;';
                div.innerHTML = `
                    <strong>${index + 1}. ${file.name}</strong><br>
                    <small>크기: ${(file.size / 1024 / 1024).toFixed(2)}MB</small><br>
                    <input type="text" name="custom_titles[]" placeholder="사용자 정의 제목 (선택)" 
                           style="width: 100%; padding: 5px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
                `;
                previewGrid.appendChild(div);
            });
        }

        // 폼 제출
        document.getElementById('bulkUploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (selectedFiles.length === 0) {
                alert('업로드할 파일을 선택해주세요.');
                return;
            }

            const formData = new FormData(e.target);
            
            // 선택된 파일들 추가
            selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });

            try {
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('submitBtn').textContent = '업로드 중...';

                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showResult(result);
                } else {
                    alert('업로드 중 오류가 발생했습니다.');
                }
            } catch (error) {
                alert('업로드 중 오류가 발생했습니다: ' + error.message);
            } finally {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').textContent = '업로드 시작';
            }
        });

        function showResult(result) {
            const resultSection = document.getElementById('resultSection');
            let html = '';
            
            if (result.success_count > 0) {
                html += `<div class="alert alert-success">
                    ✅ ${result.success_count}개 파일이 성공적으로 업로드되었습니다!
                </div>`;
            }
            
            if (result.error_count > 0) {
                html += `<div class="alert alert-danger">
                    ❌ ${result.error_count}개 파일 업로드에 실패했습니다.
                </div>`;
            }

            html += `<div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h3>업로드 결과</h3>
                <p><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>" class="btn btn-primary">게시판으로 이동</a></p>
            </div>`;

            resultSection.innerHTML = html;
            resultSection.style.display = 'block';
        }
    </script>
</body>
</html>

<?php include_once(G5_PATH.'/tail.php'); ?>
