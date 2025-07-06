<?php
// avocado-edition/skin/board/gallery-style/write.php (고급 에디터 통합 버전)
// 실시간 미리보기 + 코드 하이라이팅 + 치환자 + 모든 고급 기능

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

$g5['title'] = ($is_edit ? '글 수정' : '글쓰기') . ' - ' . $board['bo_subject'];
include_once(G5_PATH.'/head.php');
?>

<!-- 코드 하이라이팅을 위한 Prism.js -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>

<style>
/* 기본 스타일 */
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

.write-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.write-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 20px;
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

/* 기본 정보 섹션 */
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

.form-hint {
    font-size: 12px;
    color: #666;
    margin-top: 6px;
}

/* 고급 에디터 스타일 */
.editor-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.editor-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.tab-button {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    transition: all 0.3s;
}

.tab-button:hover {
    color: #333;
    background: #e9ecef;
}

.tab-button.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background: white;
}

.editor-main {
    display: flex;
    height: 600px;
}

.editor-pane {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.pane-header {
    background: #f8f9fa;
    padding: 12px 20px;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pane-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    color: #666;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #e9ecef;
    color: #333;
}

/* 에디터 툴바 */
.editor-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 10px 15px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.toolbar-group {
    display: flex;
    gap: 4px;
    padding-right: 12px;
    border-right: 1px solid #dee2e6;
}

.toolbar-group:last-child {
    border-right: none;
}

.toolbar-btn {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 4px;
}

.toolbar-btn:hover {
    background: #e9ecef;
}

.toolbar-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.editor-textarea {
    flex: 1;
    border: none;
    padding: 20px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
    line-height: 1.6;
    resize: none;
    outline: none;
    background: #fafafa;
}

.editor-textarea:focus {
    background: white;
}

/* 미리보기 영역 */
.preview-pane {
    flex: 1;
    border-left: 1px solid #e9ecef;
}

.preview-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: white;
}

/* 미리보기 스타일 */
.preview-content h1 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.preview-content h2 {
    font-size: 22px;
    font-weight: 600;
    color: #34495e;
    margin: 24px 0 16px 0;
}

.preview-content h3 {
    font-size: 18px;
    font-weight: 600;
    color: #34495e;
    margin: 20px 0 12px 0;
}

.preview-content p {
    margin-bottom: 16px;
    line-height: 1.7;
    color: #2c3e50;
}

.preview-content ul, .preview-content ol {
    margin: 16px 0 16px 20px;
}

.preview-content li {
    margin-bottom: 8px;
    line-height: 1.6;
}

.preview-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 16px;
    margin: 16px 0;
    color: #6c757d;
    font-style: italic;
}

.preview-content pre {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 16px;
    margin: 16px 0;
    overflow-x: auto;
}

.preview-content code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
}

.preview-content pre code {
    background: none;
    padding: 0;
}

/* 코드 블럭 스타일 */
.code-block {
    position: relative;
    margin: 16px 0;
    border-radius: 8px;
    overflow: hidden;
    background: #2d3748;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.code-header {
    background: #1a202c;
    padding: 8px 16px;
    font-size: 12px;
    color: #a0aec0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.code-lang {
    font-weight: 600;
    text-transform: uppercase;
}

.copy-btn {
    background: #4a5568;
    border: none;
    color: #e2e8f0;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    transition: background 0.2s;
}

.copy-btn:hover {
    background: #2d3748;
}

.copy-btn.copied {
    background: #38a169;
}

/* 치환자 기능 */
.replacer {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 12px;
    color: #1976d2;
    cursor: pointer;
    transition: all 0.2s;
}

.replacer:hover {
    background: #bbdefb;
}

/* 타이핑 애니메이션 */
.typing-effect {
    display: inline-block;
}

.typing-cursor {
    display: inline-block;
    background: #333;
    width: 2px;
    height: 1.2em;
    margin-left: 2px;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

.dots-animation {
    display: inline-block;
}

.dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #666;
    margin: 0 2px;
    opacity: 0;
    animation: dotPulse 1.4s infinite ease-in-out;
}

.dot:nth-child(1) { animation-delay: -0.32s; }
.dot:nth-child(2) { animation-delay: -0.16s; }
.dot:nth-child(3) { animation-delay: 0s; }

@keyframes dotPulse {
    0%, 80%, 100% { opacity: 0; }
    40% { opacity: 1; }
}

/* 옵션 설정 */
.option-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
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
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .editor-main {
        flex-direction: column;
        height: auto;
    }
    
    .preview-pane {
        border-left: none;
        border-top: 1px solid #e9ecef;
        max-height: 400px;
    }
    
    .editor-pane {
        min-height: 300px;
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

            <div class="form-group">
                <label for="wr_link1" class="form-label">🎵 배경음악 (BGM)</label>
                <input type="url" name="wr_link1" id="wr_link1" class="form-input" 
                       value="<?php echo $default_bgm ?>" 
                       placeholder="https://example.com/bgm.mp3">
                <div class="form-hint">MP3, WAV 등의 오디오 파일 URL을 입력하세요.</div>
            </div>
        </div>

        <!-- 고급 에디터 섹션 -->
        <div class="form-section">
            <h3 class="section-title">📄 본문 작성 (고급 에디터)</h3>
            
            <div class="editor-container">
                <!-- 탭 시스템 -->
                <div class="editor-tabs">
                    <button type="button" class="tab-button active" onclick="switchTab('editor')">📝 본문 작성</button>
                    <button type="button" class="tab-button" onclick="switchTab('preview')">👁️ 미리보기</button>
                    <button type="button" class="tab-button" onclick="switchTab('split')">📖 분할 뷰</button>
                </div>

                <!-- 메인 에디터 -->
                <div class="editor-main" id="editorMain">
                    <!-- 에디터 영역 -->
                    <div class="editor-pane" id="editorPane">
                        <div class="pane-header">
                            <span>본문 작성</span>
                            <div class="pane-actions">
                                <button type="button" class="action-btn" onclick="toggleDarkMode()">🌙 다크모드</button>
                                <button type="button" class="action-btn" onclick="insertTemplate()">📋 템플릿</button>
                            </div>
                        </div>

                        <!-- 툴바 -->
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" onclick="insertText('**', '**')" title="볼드">
                                    <strong>B</strong>
                                </button>
                                <button type="button" class="toolbar-btn" onclick="insertText('*', '*')" title="이탤릭">
                                    <em>I</em>
                                </button>
                                <button type="button" class="toolbar-btn" onclick="insertText('~~', '~~')" title="취소선">
                                    <del>S</del>
                                </button>
                                <button type="button" class="toolbar-btn" onclick="insertText('==', '==')" title="하이라이트">
                                    🎨
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" onclick="insertText('\n# ', '')" title="헤더 1">H1</button>
                                <button type="button" class="toolbar-btn" onclick="insertText('\n## ', '')" title="헤더 2">H2</button>
                                <button type="button" class="toolbar-btn" onclick="insertText('\n### ', '')" title="헤더 3">H3</button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" onclick="insertText('\n- ', '')" title="목록">📝</button>
                                <button type="button" class="toolbar-btn" onclick="insertText('\n> ', '')" title="인용">💬</button>
                                <button type="button" class="toolbar-btn" onclick="insertText('\n---\n', '')" title="구분선">➖</button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" onclick="insertCodeBlock('html')" title="HTML 코드">HTML</button>
                                <button type="button" class="toolbar-btn" onclick="insertCodeBlock('css')" title="CSS 코드">CSS</button>
                                <button type="button" class="toolbar-btn" onclick="insertCodeBlock('javascript')" title="JS 코드">JS</button>
                                <button type="button" class="toolbar-btn" onclick="insertCodeBlock('php')" title="PHP 코드">PHP</button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" onclick="insertReplacer('타이핑')" title="타이핑 효과">⌨️</button>
                                <button type="button" class="toolbar-btn" onclick="insertReplacer('점')" title="점 애니메이션">•••</button>
                                <button type="button" class="toolbar-btn" onclick="insertReplacer('복사')" title="복사 버튼">📋</button>
                            </div>
                        </div>

                        <!-- 텍스트 에어리어 -->
                        <textarea name="wr_content" class="editor-textarea" id="editorTextarea" 
                                  placeholder="여기에 내용을 입력하세요...

💡 고급 기능 사용법:

## 기본 서식
**볼드 텍스트** *이탤릭 텍스트* ~~취소선~~ ==하이라이트==

## 코드 블럭
```html
<p>HTML 코드 예시</p>
```

```css
.example { color: blue; }
```

```javascript
console.log('JavaScript 예시');
```

## 특수 효과 (치환자)
[타이핑:안녕하세요! 천천히 나타나는 텍스트입니다.]
[점:로딩중]
[복사:이 텍스트를 클릭하면 복사됩니다]

## 구조 요소
> 인용문입니다.

- 목록 아이템 1
- 목록 아이템 2

---

# 제목 1
## 제목 2
### 제목 3"><?php echo htmlspecialchars($default_content) ?></textarea>
                    </div>

                    <!-- 미리보기 영역 -->
                    <div class="preview-pane" id="previewPane">
                        <div class="pane-header">
                            <span>실시간 미리보기</span>
                            <div class="pane-actions">
                                <button type="button" class="action-btn" onclick="exportHTML()">📤 HTML 내보내기</button>
                            </div>
                        </div>
                        <div class="preview-content" id="previewContent">
                            <!-- 미리보기 내용이 여기에 실시간으로 표시됩니다 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 옵션 설정 -->
        <div class="option-section">
            <h3 class="section-title">⚙️ 게시 옵션</h3>
            
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
                    <?php echo $is_edit ? '💾 수정하기' : '🚀 발행하기' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let isDarkMode = false;
let currentTab = 'split';

// === 텍스트 삽입 함수 ===
function insertText(before, after) {
    const textarea = document.getElementById('editorTextarea');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    const newText = before + selectedText + after;
    textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
    
    const newCursorPos = start + before.length + selectedText.length;
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();
    
    updatePreview();
}

// === 코드 블럭 삽입 ===
function insertCodeBlock(language) {
    const placeholder = language === 'html' ? '<div>HTML 코드</div>' :
                       language === 'css' ? '.example { color: blue; }' :
                       language === 'javascript' ? "console.log('Hello World!');" :
                       language === 'php' ? "<?php echo 'Hello World!'; ?>" : '코드를 입력하세요';
    
    insertText(`\n\`\`\`${language}\n`, `\n\`\`\`\n`);
    
    setTimeout(() => {
        const textarea = document.getElementById('editorTextarea');
        const pos = textarea.selectionStart - 4;
        textarea.value = textarea.value.substring(0, pos) + placeholder + textarea.value.substring(pos);
        textarea.setSelectionRange(pos, pos + placeholder.length);
        updatePreview();
    }, 10);
}

// === 치환자 삽입 ===
function insertReplacer(type) {
    let text = '';
    switch(type) {
        case '타이핑':
            text = '[타이핑:여기에 타이핑될 텍스트를 입력하세요]';
            break;
        case '점':
            text = '[점:로딩중]';
            break;
        case '복사':
            text = '[복사:복사할 텍스트]';
            break;
    }
    insertText(text, '');
}

// === 미리보기 업데이트 ===
function updatePreview() {
    const textarea = document.getElementById('editorTextarea');
    const preview = document.getElementById('previewContent');
    let content = textarea.value;

    // 마크다운 파싱
    content = parseMarkdown(content);
    
    // 치환자 처리
    content = parseReplacers(content);
    
    preview.innerHTML = content;
    
    // 코드 하이라이팅 적용
    if (typeof Prism !== 'undefined') {
        Prism.highlightAll();
    }
    
    // 애니메이션 효과 적용
    applyAnimations();
}

// === 마크다운 파서 ===
function parseMarkdown(text) {
    text = text
        // 헤더
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        
        // 볼드, 이탤릭, 취소선, 하이라이트
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/~~(.*?)~~/g, '<del>$1</del>')
        .replace(/==(.*?)==/g, '<mark>$1</mark>')
        
        // 인용
        .replace(/^> (.*$)/gim, '<blockquote>$1</blockquote>')
        
        // 목록
        .replace(/^\- (.*$)/gim, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>')
        
        // 구분선
        .replace(/^---$/gim, '<hr>')
        
        // 코드 블럭
        .replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
            const language = lang || 'text';
            const id = 'code-' + Math.random().toString(36).substr(2, 9);
            return `
                <div class="code-block">
                    <div class="code-header">
                        <span class="code-lang">${language}</span>
                        <button class="copy-btn" onclick="copyCode('${id}')">📋 복사</button>
                    </div>
                    <pre><code id="${id}" class="language-${language}">${code.trim()}</code></pre>
                </div>
            `;
        })
        
        // 인라인 코드
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        
        // 줄바꿈
        .replace(/\n/g, '<br>');

    return text;
}

// === 치환자 파서 ===
function parseReplacers(text) {
    // 타이핑 효과
    text = text.replace(/\[타이핑:(.*?)\]/g, (match, content) => {
        const id = 'typing-' + Math.random().toString(36).substr(2, 9);
        return `<span class="typing-effect" id="${id}" data-text="${content}"></span>`;
    });
    
    // 점 애니메이션
    text = text.replace(/\[점:(.*?)\]/g, (match, content) => {
        return `${content}<span class="dots-animation"><span class="dot"></span><span class="dot"></span><span class="dot"></span></span>`;
    });
    
    // 복사 버튼
    text = text.replace(/\[복사:(.*?)\]/g, (match, content) => {
        const id = 'copy-' + Math.random().toString(36).substr(2, 9);
        return `<span class="replacer" id="${id}" onclick="copyToClipboard('${content.replace(/'/g, '\\\'')}')" data-text="${content}">${content} 📋</span>`;
    });

    return text;
}

// === 애니메이션 적용 ===
function applyAnimations() {
    // 타이핑 효과
    document.querySelectorAll('.typing-effect').forEach(element => {
        if (!element.dataset.animated) {
            element.dataset.animated = 'true';
            const text = element.dataset.text;
            element.innerHTML = '';
            
            let i = 0;
            const typeInterval = setInterval(() => {
                element.innerHTML += text.charAt(i);
                i++;
                if (i >= text.length) {
                    clearInterval(typeInterval);
                    element.innerHTML += '<span class="typing-cursor"></span>';
                }
            }, 100);
        }
    });
}

// === 코드 복사 기능 ===
function copyCode(id) {
    const code = document.getElementById(id);
    const text = code.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '✅ 복사됨!';
        btn.classList.add('copied');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('copied');
        }, 2000);
    }).catch(err => {
        console.error('복사 실패:', err);
        alert('복사에 실패했습니다.');
    });
}

// === 클립보드 복사 ===
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = text + ' ✅';
        
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    }).catch(err => {
        console.error('복사 실패:', err);
    });
}

// === 탭 전환 ===
function switchTab(tab) {
    const editorPane = document.getElementById('editorPane');
    const previewPane = document.getElementById('previewPane');
    const buttons = document.querySelectorAll('.tab-button');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    if (tab === 'editor') {
        editorPane.style.display = 'flex';
        previewPane.style.display = 'none';
    } else if (tab === 'preview') {
        editorPane.style.display = 'none';
        previewPane.style.display = 'flex';
    } else if (tab === 'split') {
        editorPane.style.display = 'flex';
        previewPane.style.display = 'flex';
    }
    
    currentTab = tab;
    updatePreview();
}

// === 다크 모드 토글 ===
function toggleDarkMode() {
    isDarkMode = !isDarkMode;
    document.body.classList.toggle('dark-mode', isDarkMode);
}

// === 템플릿 삽입 ===
function insertTemplate() {
    const templates = {
        '웹툰': `# 웹툰 제목

## 등장인물
- **주인공**: 캐릭터 설명
- **조연**: 캐릭터 설명

## 줄거리
[타이핑:흥미진진한 스토리가 펼쳐집니다...]

---

*다음화에서 계속...*`,

        '소설': `# 소설 제목

> "인상적인 첫 문장"

## 프롤로그

[타이핑:이야기는 이렇게 시작되었다...]

---

**키워드**: #소설 #판타지 #로맨스`,

        '코드': `# 프로젝트 제목

## 설명
프로젝트에 대한 간단한 설명입니다.

## 사용 기술
\`\`\`html
<div class="example">HTML 예시</div>
\`\`\`

\`\`\`css
.example {
    color: #333;
    font-size: 16px;
}
\`\`\`

\`\`\`javascript
console.log('Hello World!');
\`\`\`

[복사:GitHub 링크: https://github.com/username/repo]`
    };

    const templateKeys = Object.keys(templates);
    const choice = prompt(`사용할 템플릿을 선택하세요:\n${templateKeys.map((k, i) => `${i+1}. ${k}`).join('\n')}`);
    
    if (choice && choice >= 1 && choice <= templateKeys.length) {
        const selectedTemplate = templates[templateKeys[choice - 1]];
        document.getElementById('editorTextarea').value = selectedTemplate;
        updatePreview();
    }
}

// === HTML 내보내기 ===
function exportHTML() {
    const content = document.getElementById('previewContent').innerHTML;
    const blob = new Blob([content], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'preview.html';
    a.click();
    URL.revokeObjectURL(url);
}

// === 19금 경고 토글 ===
function toggleAdultWarning() {
    const checkbox = document.getElementById('adultContent');
    const warning = document.getElementById('adultWarning');
    warning.style.display = checkbox.checked ? 'block' : 'none';
}

// === 미리보기 ===
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

// === 이벤트 리스너 ===
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('editorTextarea');
    
    // 실시간 미리보기 업데이트
    textarea.addEventListener('input', updatePreview);
    
    // 초기 미리보기 생성
    updatePreview();
    
    // 키보드 단축키
    textarea.addEventListener('keydown', function(e) {
        // Ctrl+B: 볼드
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            insertText('**', '**');
        }
        
        // Ctrl+I: 이탤릭
        if (e.ctrlKey && e.key === 'i') {
            e.preventDefault();
            insertText('*', '*');
        }
        
        // Tab: 들여쓰기
        if (e.key === 'Tab') {
            e.preventDefault();
            insertText('  ', '');
        }
    });
    
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
    
    // 폼 제출 전 검증
    document.getElementById('fwrite').addEventListener('submit', function(e) {
        const category = document.getElementById('ca_name').value.trim();
        const title = document.getElementById('wr_subject').value.trim();
        const content = document.getElementById('editorTextarea').value.trim();
        
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
