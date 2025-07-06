<?php
// avocado-edition/skin/board/gallery-style/view.php
// 포스트 상세 뷰 (웹툰 뷰어 + 텍스트 뷰)

if (!defined('_GNUBOARD_')) exit;

// 현재 글 정보
$wr_id = $view['wr_id'];
$ca_name = $view['ca_name'];

// 같은 시리즈의 이전/다음 글 찾기
$prev_next_sql = "SELECT wr_id, wr_subject, wr_1 as episode_num FROM {$g5['write_prefix']}{$bo_table} 
                  WHERE ca_name = '{$ca_name}' AND wr_is_comment = 0 
                  ORDER BY wr_num DESC, wr_id DESC";
$prev_next_result = sql_query($prev_next_sql);

$episodes = array();
$current_index = -1;
$episode_index = 0;

while($row = sql_fetch_array($prev_next_result)) {
    $episodes[] = $row;
    if($row['wr_id'] == $wr_id) {
        $current_index = $episode_index;
    }
    $episode_index++;
}

$prev_episode = ($current_index > 0) ? $episodes[$current_index - 1] : null;
$next_episode = ($current_index < count($episodes) - 1) ? $episodes[$current_index + 1] : null;

// 첨부 파일들 가져오기
$files = array();
$file_sql = "SELECT * FROM {$g5['board_file_table']} 
             WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' 
             AND bf_file != '' ORDER BY bf_no";
$file_result = sql_query($file_sql);
while($file_row = sql_fetch_array($file_result)) {
    $files[] = $file_row;
}

// 컨텐츠 타입 판단 (이미지가 있으면 웹툰 뷰, 없으면 텍스트 뷰)
$is_webtoon_view = !empty($files);
$has_bgm = !empty($view['wr_link1']); // wr_link1에 BGM URL 저장

$g5['title'] = $view['wr_subject'];
include_once(G5_PATH.'/head.php');
?>

<style>
/* 전체 레이아웃 */
.post-container {
    background: #f8f9fa;
    min-height: 100vh;
    position: relative;
}

/* 헤더 */
.post-header {
    background: white;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.header-content {
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.back-to-series {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    color: #666;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.back-to-series:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.post-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    text-align: center;
    flex: 1;
    margin: 0 20px;
}

.view-controls {
    display: flex;
    gap: 10px;
}

.control-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    color: #666;
    font-size: 14px;
    transition: all 0.2s;
}

.control-btn:hover {
    background: #e9ecef;
}

.control-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

/* 웹툰 뷰어 */
.webtoon-viewer {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: white;
    min-height: calc(100vh - 200px);
}

.webtoon-images {
    display: flex;
    flex-direction: column;
    gap: 0;
    align-items: center;
}

.webtoon-image {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.maximize-btn {
    position: fixed;
    top: 80px;
    right: 20px;
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    z-index: 1000;
    transition: all 0.3s;
}

.maximize-btn:hover {
    background: rgba(0,0,0,0.9);
    transform: scale(1.1);
}

/* 텍스트 뷰 */
.text-viewer {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px;
    background: white;
    min-height: calc(100vh - 200px);
    line-height: 1.8;
}

.text-content {
    font-size: 16px;
    color: #333;
    line-height: 1.8;
}

.text-content h1, .text-content h2, .text-content h3 {
    margin: 30px 0 20px 0;
    color: #2c3e50;
}

.text-content p {
    margin-bottom: 20px;
}

.text-content img {
    max-width: 100%;
    height: auto;
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* 하단 네비게이션 */
.bottom-navigation {
    background: white;
    border-top: 1px solid #e9ecef;
    padding: 20px;
    position: sticky;
    bottom: 0;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
}

.nav-content {
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.nav-episode {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.series-info {
    font-size: 14px;
    color: #666;
}

.series-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
}

.episode-info {
    font-size: 12px;
    color: #999;
}

/* BGM 컨트롤 */
.bgm-control {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 20px;
    border: 1px solid #dee2e6;
}

.bgm-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    border-radius: 50%;
    transition: background 0.2s;
}

.bgm-btn:hover {
    background: #e9ecef;
}

.volume-slider {
    width: 80px;
    height: 4px;
    background: #dee2e6;
    border-radius: 2px;
    outline: none;
    cursor: pointer;
}

.volume-slider::-webkit-slider-thumb {
    appearance: none;
    width: 12px;
    height: 12px;
    background: #007bff;
    border-radius: 50%;
    cursor: pointer;
}

/* 네비게이션 버튼 */
.nav-buttons {
    display: flex;
    gap: 10px;
}

.nav-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    color: #666;
    font-size: 14px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.nav-btn:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.nav-btn.primary {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.nav-btn.primary:hover {
    background: #0056b3;
}

/* 전체화면 모드 */
.fullscreen-mode {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: black;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: auto;
}

.fullscreen-content {
    max-width: 100%;
    max-height: 100%;
    padding: 20px;
}

.fullscreen-content img {
    max-width: 100%;
    max-height: 100vh;
    object-fit: contain;
}

.fullscreen-close {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.9);
    border: none;
    padding: 10px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    z-index: 10001;
}

/* 반응형 */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 10px;
        padding: 10px;
    }
    
    .post-title {
        margin: 0;
        text-align: center;
    }
    
    .webtoon-viewer, .text-viewer {
        padding: 15px;
    }
    
    .nav-content {
        flex-direction: column;
        gap: 15px;
    }
    
    .nav-episode {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .nav-buttons {
        width: 100%;
        justify-content: space-between;
    }
    
    .maximize-btn {
        top: 120px;
        right: 15px;
    }
}
</style>

<div class="post-container">
    <!-- 헤더 -->
    <div class="post-header">
        <div class="header-content">
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>&sca=<?php echo urlencode($ca_name) ?>" class="back-to-series">
                ← 시리즈로 돌아가기
            </a>
            
            <h1 class="post-title"><?php echo $view['wr_subject'] ?></h1>
            
            <div class="view-controls">
                <?php if(!empty($files)) { ?>
                <button class="control-btn active" id="webtoonMode" onclick="switchViewMode('webtoon')">
                    🖼️ 웹툰뷰
                </button>
                <?php } ?>
                <button class="control-btn <?php echo empty($files) ? 'active' : '' ?>" id="textMode" onclick="switchViewMode('text')">
                    📝 텍스트뷰
                </button>
                <?php if ($is_admin || $member['mb_id'] === $view['mb_id']) { ?>
                <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $wr_id ?>" class="control-btn">
                    ✏️ 수정
                </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 웹툰 뷰어 -->
    <?php if(!empty($files)) { ?>
    <div class="webtoon-viewer" id="webtoonViewer">
        <div class="webtoon-images">
            <?php foreach($files as $file) { ?>
                <?php 
                $file_path = G5_DATA_URL.'/file/'.$bo_table.'/'.$file['bf_file'];
                $file_ext = strtolower(pathinfo($file['bf_file'], PATHINFO_EXTENSION));
                if(in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                ?>
                <img src="<?php echo $file_path ?>" alt="<?php echo $file['bf_source'] ?>" class="webtoon-image">
                <?php } ?>
            <?php } ?>
        </div>
        
        <button class="maximize-btn" onclick="toggleFullscreen()" title="전체화면">
            ⛶
        </button>
    </div>
    <?php } ?>

    <!-- 텍스트 뷰어 -->
    <div class="text-viewer" id="textViewer" style="<?php echo !empty($files) ? 'display: none;' : '' ?>">
        <div class="text-content">
            <?php echo $view['wr_content'] ?>
        </div>
    </div>

    <!-- 하단 네비게이션 -->
    <div class="bottom-navigation">
        <div class="nav-content">
            <div class="nav-episode">
                <div class="series-info">
                    <div class="series-title"><?php echo $ca_name ?></div>
                    <div class="episode-info">
                        <?php echo ($current_index + 1) ?> / <?php echo count($episodes) ?>화 • 
                        <?php echo date('Y.m.d', strtotime($view['wr_datetime'])) ?> • 
                        조회 <?php echo number_format($view['wr_hit']) ?>
                    </div>
                </div>

                <?php if($has_bgm) { ?>
                <div class="bgm-control">
                    <button class="bgm-btn" onclick="toggleBGM()" id="bgmToggle">
                        🔊
                    </button>
                    <input type="range" class="volume-slider" id="volumeSlider" 
                           min="0" max="100" value="50" onchange="setVolume(this.value)">
                    <audio id="bgmAudio" loop preload="auto">
                        <source src="<?php echo $view['wr_link1'] ?>" type="audio/mpeg">
                    </audio>
                </div>
                <?php } ?>
            </div>

            <div class="nav-buttons">
                <?php if($prev_episode) { ?>
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $prev_episode['wr_id'] ?>" class="nav-btn">
                    ← 이전화
                </a>
                <?php } else { ?>
                <button class="nav-btn" disabled>← 이전화</button>
                <?php } ?>

                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>&sca=<?php echo urlencode($ca_name) ?>" class="nav-btn primary">
                    📋 목록
                </a>

                <?php if($next_episode) { ?>
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $next_episode['wr_id'] ?>" class="nav-btn">
                    다음화 →
                </a>
                <?php } else { ?>
                <button class="nav-btn" disabled>다음화 →</button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- 전체화면 모드 -->
<div class="fullscreen-mode" id="fullscreenMode" style="display: none;">
    <button class="fullscreen-close" onclick="exitFullscreen()">✕</button>
    <div class="fullscreen-content" id="fullscreenContent">
        <!-- 전체화면 이미지들이 여기에 복사됩니다 -->
    </div>
</div>

<script>
let isPlaying = false;
let currentViewMode = '<?php echo !empty($files) ? "webtoon" : "text" ?>';

// 뷰 모드 전환
function switchViewMode(mode) {
    const webtoonViewer = document.getElementById('webtoonViewer');
    const textViewer = document.getElementById('textViewer');
    const webtoonBtn = document.getElementById('webtoonMode');
    const textBtn = document.getElementById('textMode');

    if (mode === 'webtoon' && webtoonViewer) {
        webtoonViewer.style.display = 'block';
        textViewer.style.display = 'none';
        webtoonBtn.classList.add('active');
        textBtn.classList.remove('active');
        currentViewMode = 'webtoon';
    } else {
        if (webtoonViewer) webtoonViewer.style.display = 'none';
        textViewer.style.display = 'block';
        if (webtoonBtn) webtoonBtn.classList.remove('active');
        textBtn.classList.add('active');
        currentViewMode = 'text';
    }
}

// 전체화면 토글
function toggleFullscreen() {
    const fullscreenMode = document.getElementById('fullscreenMode');
    const fullscreenContent = document.getElementById('fullscreenContent');
    const webtoonImages = document.querySelectorAll('.webtoon-image');

    if (fullscreenMode.style.display === 'none') {
        // 전체화면 진입
        fullscreenContent.innerHTML = '';
        webtoonImages.forEach(img => {
            const clonedImg = img.cloneNode(true);
            clonedImg.style.marginBottom = '10px';
            fullscreenContent.appendChild(clonedImg);
        });
        
        fullscreenMode.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        exitFullscreen();
    }
}

function exitFullscreen() {
    const fullscreenMode = document.getElementById('fullscreenMode');
    fullscreenMode.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC 키로 전체화면 나가기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        exitFullscreen();
    }
});

// BGM 제어
function toggleBGM() {
    const audio = document.getElementById('bgmAudio');
    const toggleBtn = document.getElementById('bgmToggle');
    
    if (!audio) return;

    if (isPlaying) {
        audio.pause();
        toggleBtn.textContent = '🔇';
        isPlaying = false;
    } else {
        audio.play().catch(e => {
            console.log('BGM 자동재생이 차단되었습니다. 버튼을 클릭해주세요.');
        });
        toggleBtn.textContent = '🔊';
        isPlaying = true;
    }
}

function setVolume(value) {
    const audio = document.getElementById('bgmAudio');
    if (audio) {
        audio.volume = value / 100;
    }
}

// BGM 자동재생 시도
document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('bgmAudio');
    if (audio) {
        // 볼륨 초기값 설정
        audio.volume = 0.5;
        
        // 자동재생 시도 (브라우저에서 차단될 수 있음)
        audio.play().then(() => {
            isPlaying = true;
            document.getElementById('bgmToggle').textContent = '🔊';
        }).catch(e => {
            console.log('BGM 자동재생이 차단되었습니다.');
            isPlaying = false;
            document.getElementById('bgmToggle').textContent = '🔇';
        });
    }
});

// 키보드 네비게이션
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        // 이전화로 이동
        const prevBtn = document.querySelector('.nav-btn[href*="wr_id"]:first-of-type');
        if (prevBtn && !prevBtn.disabled) {
            window.location.href = prevBtn.href;
        }
    } else if (e.key === 'ArrowRight') {
        // 다음화로 이동  
        const nextBtn = document.querySelector('.nav-btn[href*="wr_id"]:last-of-type');
        if (nextBtn && !nextBtn.disabled) {
            window.location.href = nextBtn.href;
        }
    }
});

// 이미지 지연 로딩 (성능 최적화)
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// 조회수 증가 (AJAX)
function incrementView() {
    fetch('<?php echo G5_BBS_URL ?>/ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=increment_view&bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $wr_id ?>'
    });
}

// 페이지 로드 시 조회수 증가
window.addEventListener('load', incrementView);
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
