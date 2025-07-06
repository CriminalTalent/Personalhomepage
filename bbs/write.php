<?php
// avocado-edition/skin/board/gallery-style/write.php
// 고급 글쓰기 기능 (시리즈, 썸네일, BGM, 19+, 비밀글 등)

if (!defined('_GNUBOARD_')) exit;

// 권한 체크
if (!$member['mb_id']) {
    alert('회원만 글쓰기가 가능합니다.');
}

// 수정 모드인지 확인
$is_edit = !empty($wr_id);
$edit_data = null;

if ($is_edit) {
    $edit_sql = "SELECT * FROM {$g5['write_prefix']}{$bo_table} WHERE wr_id = '$wr_id'";
    $edit_data = sql_fetch($edit_sql);
    
    if (!$edit_data) {
        alert('존재하지 않는 게시글입니다.');
    }
    
    // 권한 체크
    if (!$is_admin && $member['mb_id'] !== $edit_data['mb_id']) {
        alert('수정 권한이 없습니다.');
    }
}

// 기본값 설정
$default_category = $_GET['category'] ?? $_GET['ca_name'] ?? ($edit_data['ca_name'] ?? '');
$default_title = $edit_data['wr_subject'] ?? '';
$default_content = $edit_data['wr_content'] ?? '';
$default_episode = $edit_data['wr_1'] ?? '';
$default_bgm = $edit_data['wr_link1'] ?? '';
$default_is_secret = !empty($edit_data['wr_password']);
$default_is_adult = strpos($edit_data['wr_option'], 'adult') !== false;

// 기존 파일들 가져오기 (수정 모드)
$existing_files = array();
if ($is_edit) {
    $file_sql = "SELECT * FROM {$g5['board_file_table']} 
                 WHERE bo_table = '$bo_table' AND wr_id = '$wr_id' 
                 ORDER BY bf_no";
    $file_result = sql_query($file_sql);
    while ($file_row = sql_fetch_array($file_result)) {
        $existing_files[] = $file_row;
    }
}

$g5['title'] = ($is_edit ? '글 수정' : '글쓰기') . ' - ' . $board['bo_subject'];
include_once(G5_PATH.'/head.php');
?>

<style>
/* 전체 레이아웃 */
.write-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.write-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.write-title {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}

.breadcrumb {
    color: #666;
    font-size: 14px;
}

.breadcrumb a {
    color: #007bff;
    text-decoration: none;
}

/* 폼 섹션 */
.form-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-label .required {
    color: #dc3545;
    margin-left: 4px;
}

.form-input, .form-textarea, .form-select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.6;
}

.form-hint {
    font-size: 12px;
    color: #666;
    margin-top: 6px;
}

/* 옵션 체크박스 */
.option-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.option-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label {
    font-size: 14px;
    color: #333;
    cursor: pointer;
}

.adult-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 12px;
    margin-top: 10px;
    font-size: 13px;
    color: #856404;
    display: none;
}

/* 파일 업로드 */
.upload-section {
    border: 2px dashed #ddd;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    background: #fafafa;
    transition: all 0.3s;
    cursor: pointer;
    margin-bottom: 20px;
}

.upload-section:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.upload-section.dragover {
    border-color: #007bff;
    background: #e6f3ff;
    transform: scale(1.02);
}

.upload-icon {
    font-size: 48px;
    color: #999;
    margin-bottom: 15px;
}

.upload-text {
    font-size: 16px;
    color: #333;
    margin-bottom: 8px;
}

.upload-hint {
    font-size: 13px;
    color: #666;
}

.file-input {
    display: none;
}

/* 미리보기 */
.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.preview-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    position: relative;
}

.preview-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    background: #f8f9fa;
}

.preview-info {
    padding: 10px;
    font-size: 12px;
    color: #666;
}

.preview-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220,53,69,0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 12px;
}

/* 특별 필드 */
.image-upload-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.image-upload-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #fafafa;
    cursor: pointer;
    transition: all 0.3s;
}

.image-upload-item:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.image-upload-preview {
    width: 100%;
    height: 100px;
    border-radius: 6px;
    object-fit: cover;
    margin-bottom: 10px;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #999;
}

/* 에디터 */
.editor-container {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.editor-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 10px 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.editor-btn {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.editor-btn:hover {
    background: #e9ecef;
}

/* 액션 버튼 */
.action-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
}

/* 반응형 */
@media (max-width: 768px) {
    .write-container {
        padding: 15px;
    }
    
    .form-row, .image-upload-group {
        grid-template-columns: 1fr;
    }
    
    .option-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="write-container">
    <!-- 헤더 -->
    <div class="write-header">
        <div class="breadcrumb">
            <a href="<?php echo G5_URL ?>">홈</a> > 
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>"><?php echo $board['bo_subject'] ?></a> > 
            <?php echo $is_edit ? '글 수정' : '글쓰기' ?>
        </div>
        <h1 class="write-title">
            <?php echo $is_edit ? '✏️ 글 수정' : '✍️ 새 글 작성' ?>
        </h1>
    </div>

    <form name="fwrite" id="fwrite" action="<?php echo G5_BBS_URL ?>/write_update.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="spt" value="<?php echo $spt ?>">
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">

        <!-- 기본 정보 섹션 -->
        <div class="form-section">
            <h3 class="section-title">📝 기본 정보</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ca_name" class="form-label">시리즈 (카테고리) <span class="required">*</span></label>
                    <input type="text" name="ca_name" id="ca_name" class="form-input" 
                           value="<?php echo $default_category ?>" 
                           placeholder="예: 내 웹툰 제목" required>
                    <div class="form-hint">시리즈명을 입력하세요. 같은 시리즈의 작품들이 함께 묶입니다.</div>
                </div>
                
                <div class="form-group">
                    <label for="wr_1" class="form-label">회차</label>
                    <input type="number" name="wr_1" id="wr_1" class="form-input" 
                           value="<?php echo $default_episode ?>" 
                           placeholder="회차 번호" min="1">
                    <div class="form-hint">회차 번호를 입력하세요. (선택사항)</div>
                </div>
            </div>

            <div class="form-group">
                <label for="wr_subject" class="form-label">제목 <span class="required">*</span></label>
                <input type="text" name="wr_subject" id="wr_subject" class="form-input" 
                       value="<?php echo $default_title ?>" 
                       placeholder="포스트 제목을 입력하세요" required>
            </div>

            <div class="form-group">
                <label for="wr_2" class="form-label">제목 (영문)</label>
                <input type="text" name="wr_2" id="wr_2" class="form-input" 
                       value="<?php echo $edit_data['wr_2'] ?? '' ?>" 
                       placeholder="English Title (선택사항)">
            </div>

            <div class="form-group">
                <label for="wr_3" class="form-label">소개</label>
                <textarea name="wr_3" id="wr_3" class="form-textarea" 
                          placeholder="작품이나 시리즈 소개를 입력하세요"><?php echo $edit_data['wr_3'] ?? '' ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="wr_4" class="form-label">장르</label>
                    <input type="text" name="wr_4" id="wr_4" class="form-input" 
                           value="<?php echo $edit_data['wr_4'] ?? '' ?>" 
                           placeholder="판타지, 로맨스, 액션 등">
                </div>
                
                <div class="form-group">
                    <label for="wr_5" class="form-label">키워드</label>
                    <input type="text" name="wr_5" id="wr_5" class="form-input" 
                           value="<?php echo $edit_data['wr_5'] ?? '' ?>" 
                           placeholder="#태그1 #태그2 #태그3">
                    <div class="form-hint"># 기호를 사용해서 태그를 입력하세요</div>
                </div>
            </div>
        </div>

        <!-- 이미지 업로드 섹션 -->
        <div class="form-section">
            <h3 class="section-title">🖼️ 이미지 설정</h3>
            
            <div class="image-upload-group">
                <div class="form-group">
                    <label class="form-label">대표 이미지 (썸네일)</label>
                    <div class="image-upload-item" onclick="document.getElementById('thumbnailInput').click()">
                        <div class="image-upload-preview" id="thumbnailPreview">📷</div>
                        <div>클릭하여 썸네일 업로드</div>
                        <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*" class="file-input" onchange="previewThumbnail(this)">
                    </div>
                    <div class="form-hint">시리즈 목록에서 표시될 썸네일 이미지입니다.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">배경 이미지</label>
                    <div class="image-upload-item" onclick="document.getElementById('backgroundInput').click()">
                        <div class="image-upload-preview" id="backgroundPreview">🎨</div>
                        <div>클릭하여 배경 이미지 업로드</div>
                        <input type="file" id="backgroundInput" name="background" accept="image/*" class="file-input" onchange="previewBackground(this)">
                    </div>
                    <div class="form-hint">시리즈 페이지의 배경으로 사용됩니다.</div>
                </div>
            </div>

            <div class="form-group">
                <label for="wr_6" class="form-label">배경 이미지 URL</label>
                <input type="url" name="wr_6" id="wr_6" class="form-input" 
                       value="<?php echo $edit_data['wr_6'] ?? '' ?>" 
                       placeholder="https://example.com/background.jpg">
                <div class="form-hint">외부 이미지 URL을 입력하거나 위에서 직접 업로드하세요.</div>
            </div>
        </div>

        <!-- 본문 파일 업로드 -->
        <div class="form-section">
            <h3 class="section-title">📁 파일 업로드</h3>
            
            <div class="upload-section" id="fileUploadZone">
                <div class="upload-icon">📁</div>
                <div class="upload-text">파일을 드래그하여 업로드하세요</div>
                <div class="upload-hint">
                    이미지, 동영상, 문서 등을 업로드할 수 있습니다<br>
                    <small>최대 10MB, 여러 파일 동시 업로드 가능</small>
                </div>
                <input type="file" id="fileInput" name="bf_file[]" multiple class="file-input">
            </div>

            <div class="preview-grid" id="filePreviewGrid">
                <!-- 업로드된 파일 미리보기가 여기에 표시됩니다 -->
            </div>
        </div>

        <!-- 본문 작성 -->
        <div class="form-section">
            <h3 class="section-title">📄 본문 작성</h3>
            
            <div class="editor-container">
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" onclick="insertText('**', '**')"><strong>B</strong></button>
                    <button type="button" class="editor-btn" onclick="insertText('*', '*')"><em>I</em></button>
                    <button type="button" class="editor-btn" onclick="insertText('~~', '~~')"><del>S</del></button>
                    <button type="button" class="editor-btn" onclick="insertText('\n# ', '')">H1</button>
                    <button type="button" class="editor-btn" onclick="insertText('\n## ', '')">H2</button>
                    <button type="button" class="editor-btn" onclick="insertText('\n- ', '')">목록</button>
                    <button type="button" class="editor-btn" onclick="insertText('\n> ', '')">인용</button>
                    <button type="button" class="editor-btn" onclick="insertText('\n```\n', '\n```')">코드</button>
                </div>
                <textarea name="wr_content" id="wr_content" class="form-textarea" 
                          style="border: none; border-radius: 0; min-height: 300px;"
                          placeholder="내용을 입력하세요..."><?php echo $default_content ?></textarea>
            </div>
            <div class="form-hint">마크다운 문법을 지원합니다. 위 버튼들을 활용해보세요!</div>
        </div>

        <!-- BGM 설정 -->
        <div class="form-section">
            <h3 class="section-title">🎵 배경음악 (BGM)</h3>
            
            <div class="form-group">
                <label for="wr_link1" class="form-label">BGM URL</label>
                <input type="url" name="wr_link1" id="wr_link1" class="form-input" 
                       value="<?php echo $default_bgm ?>" 
                       placeholder="https://example.com/bgm.mp3">
                <div class="form-hint">MP3, WAV 등의 오디오 파일 URL을 입력하세요. 포스트 조회 시 자동으로 재생됩니다.</div>
            </div>
        </div>

        <!-- 옵션 설정 -->
        <div class="option-section">
            <h3 class="section-title">⚙️ 옵션</h3>
            
            <div class="option-grid">
                <div class="checkbox-group">
                    <input type="checkbox" id="secretPost" name="secret_post" class="checkbox-input" 
                           <?php echo $default_is_secret ? 'checked' : '' ?>>
                    <label for="secretPost" class="checkbox-label">🔒 비밀글</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="adultContent" name="adult_content" class="checkbox-input" 
                           <?php echo $default_is_adult ? 'checked' : '' ?> onchange="toggleAdultWarning()">
                    <label for="adultContent" class="checkbox-label">🔞 19금 콘텐츠</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="allowComment" name="allow_comment" class="checkbox-input" checked>
                    <label for="allowComment" class="checkbox-label">💬 댓글 허용</label>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="useHtml" name="use_html" class="checkbox-input" checked>
                    <label for="useHtml" class="checkbox-label">📝 HTML 사용</label>
                </div>
            </div>

            <div class="adult-warning" id="adultWarning">
                <strong>⚠️ 주의:</strong> 19금 콘텐츠로 표시된 게시물은 일반 사용자에게는 제한적으로 표시됩니다.
                관련 법령을 준수하여 적절한 콘텐츠만 업로드해주세요.
            </div>

            <div class="form-group" id="secretPasswordGroup" style="display: none; margin-top: 20px;">
                <label for="wr_password" class="form-label">비밀번호</label>
                <input type="password" name="wr_password" id="wr_password" class="form-input" 
                       placeholder="비밀글 비밀번호 (4자 이상)" minlength="4">
                <div class="form-hint">비밀글로 설정 시 이 비밀번호로 보호됩니다.</div>
            </div>
        </div>

        <!-- 액션 버튼 -->
        <div class="action-section">
            <div class="action-buttons">
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>" class="btn btn-secondary">
                    ❌ 취소
                </a>
                
                <button type="button" class="btn btn-success" onclick="previewPost()">
                    👁️ 미리보기
                </button>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $is_edit ? '💾 수정하기' : '📝 발행하기' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let uploadedFiles = [];

// 파일 드래그 앤 드롭
const fileUploadZone = document.getElementById('fileUploadZone');
const fileInput = document.getElementById('fileInput');

fileUploadZone.addEventListener('click', () => fileInput.click());

fileUploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileUploadZone.classList.add('dragover');
});

fileUploadZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    fileUploadZone.classList.remove('dragover');
});

fileUploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    fileUploadZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

// 파일 처리
function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`${file.name}은 10MB를 초과합니다.`);
            return;
        }
        
        uploadedFiles.push(file);
        addFilePreview(file);
    });
}

// 파일 미리보기 추가
function addFilePreview(file) {
    const previewGrid = document.getElementById('filePreviewGrid');
    const previewItem = document.createElement('div');
    previewItem.className = 'preview-item';
    
    const isImage = file.type.startsWith('image/');
    const fileURL = URL.createObjectURL(file);
    
    previewItem.innerHTML = `
        ${isImage ? 
            `<img src="${fileURL}" class="preview-image" alt="${file.name}">` :
            `<div class="preview-image" style="display: flex; align-items: center; justify-content: center; font-size: 24px;">📄</div>`
        }
        <div class="preview-info">
            <div style="font-weight: 600; margin-bottom: 4px;">${file.name}</div>
            <div>${(file.size / 1024 / 1024).toFixed(2)}MB</div>
        </div>
        <button type="button" class="preview-remove" onclick="removeFile(this, '${file.name}')">×</button>
    `;
    
    previewGrid.appendChild(previewItem);
}

// 파일 제거
function removeFile(button, fileName) {
    uploadedFiles = uploadedFiles.filter(file => file.name !== fileName);
    button.closest('.preview-item').remove();
}

// 썸네일 미리보기
function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnailPreview').innerHTML = 
                `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 배경 이미지 미리보기
function previewBackground(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('backgroundPreview').innerHTML = 
                `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 19금 경고 토글
function toggleAdultWarning() {
    const checkbox = document.getElementById('adultContent');
    const warning = document.getElementById('adultWarning');
    warning.style.display = checkbox.checked ? 'block' : 'none';
}

// 비밀글 비밀번호 토글
document.getElementById('secretPost').addEventListener('change', function() {
    const passwordGroup = document.getElementById('secretPasswordGroup');
    passwordGroup.style.display = this.checked ? 'block' : 'none';
    
    if (this.checked) {
        document.getElementById('wr_password').required = true;
    } else {
        document.getElementById('wr_password').required = false;
    }
});

// 에디터 텍스트 삽입
function insertText(before, after) {
    const textarea = document.getElementById('wr_content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    const newText = before + selectedText + after;
    textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
    
    // 커서 위치 조정
    const newCursorPos = start + before.length + selectedText.length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();
}

// 미리보기
function previewPost() {
    const form = document.getElementById('fwrite');
    const originalAction = form.action;
    const originalTarget = form.target;
    
    form.action = '<?php echo G5_BBS_URL ?>/preview.php';
    form.target = '_blank';
    form.submit();
    
    form.action = originalAction;
    form.target = originalTarget;
}

// 폼 제출 전 검증
document.getElementById('fwrite').addEventListener('submit', function(e) {
    const category = document.getElementById('ca_name').value.trim();
    const title = document.getElementById('wr_subject').value.trim();
    const content = document.getElementById('wr_content').value.trim();
    
    if (!category) {
        alert('시리즈(카테고리)를 입력해주세요.');
        e.preventDefault();
        return;
    }
    
    if (!title) {
        alert('제목을 입력해주세요.');
        e.preventDefault();
        return;
    }
    
    if (!content) {
        alert('내용을 입력해주세요.');
        e.preventDefault();
        return;
    }
    
    // 비밀글 비밀번호 검증
    const isSecret = document.getElementById('secretPost').checked;
    const password = document.getElementById('wr_password').value;
    
    if (isSecret && password.length < 4) {
        alert('비밀글 비밀번호는 4자 이상이어야 합니다.');
        e.preventDefault();
        return;
    }
});

// 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 19금 체크 상태에 따른 경고 표시
    toggleAdultWarning();
    
    // 비밀글 체크 상태에 따른 비밀번호 필드 표시
    const secretCheckbox = document.getElementById('secretPost');
    if (secretCheckbox.checked) {
        document.getElementById('secretPasswordGroup').style.display = 'block';
        document.getElementById('wr_password').required = true;
    }
});
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
