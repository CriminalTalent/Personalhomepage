<?php
// my-skins/gallery-style/list.php
// 아보카도 에디션 카드 갤러리 스킨 + 일괄 업로드 기능

if (!defined('_GNUBOARD_')) exit;

// 게시판 설정
$g5['title'] = $board['bo_subject'];
include_once(G5_PATH.'/head.php');

// 현재 선택된 카테고리가 있으면 시리즈 상세 모드
$is_series_view = !empty($sca);

if($is_series_view) {
    // 시리즈 정보 가져오기 (해당 카테고리의 최신 글 정보)
    $series_info_sql = "SELECT * FROM {$g5['board_table']} 
                       WHERE bo_table = '{$bo_table}' AND ca_name = '{$sca}' 
                       ORDER BY wr_datetime DESC LIMIT 1";
    $series_info = sql_fetch($series_info_sql);
    
    // 시리즈 썸네일 (첫 번째 이미지)
    $series_thumb = '';
    if($series_info) {
        $thumb_sql = "SELECT bf_file FROM {$g5['board_file_table']} 
                     WHERE bo_table = '{$bo_table}' AND wr_id = '{$series_info['wr_id']}' 
                     AND bf_file != '' ORDER BY bf_no LIMIT 1";
        $thumb_result = sql_fetch($thumb_sql);
        if($thumb_result) {
            $series_thumb = G5_DATA_URL.'/file/'.$bo_table.'/'.$thumb_result['bf_file'];
        }
    }
}
?>

<style>
/* 시리즈 상세 뷰 스타일 */
.series-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

/* 뒤로가기 */
.back-nav {
    margin-bottom: 20px;
}

.back-btn {
    background: white;
    border: 1px solid #ddd;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    color: #666;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.back-btn:hover {
    background: #f8f9fa;
}

/* 시리즈 헤더 */
.series-header {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.series-banner {
    height: 300px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.series-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.series-banner::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
}

.series-info {
    padding: 30px;
    position: relative;
}

.series-meta-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.series-title-area h1 {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
}

.series-subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
}

.series-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-btn {
    background: white;
    border: 1px solid #ddd;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    color: #666;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.action-btn:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
}

.action-btn.primary {
    background: #333;
    color: white;
    border-color: #333;
}

.action-btn.primary:hover {
    background: #555;
}

.action-btn.bulk-upload {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.action-btn.bulk-upload:hover {
    background: #218838;
}

.series-description {
    font-size: 14px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 20px;
}

.series-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.series-tag {
    background: #f1f3f4;
    color: #5f6368;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
}

.series-stats {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: #666;
    flex-wrap: wrap;
}

/* 포스트 목록 섹션 */
.posts-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.posts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.posts-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.posts-controls {
    display: flex;
    gap: 10px;
}

.sort-btn {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    color: #666;
}

.sort-btn.active {
    background: #333;
    color: white;
    border-color: #333;
}

/* 에피소드 리스트 */
.episode-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.episode-item {
    display: flex;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #f5f5f5;
    transition: background-color 0.2s;
    cursor: pointer;
}

.episode-item:hover {
    background-color: #fafafa;
    margin: 0 -30px;
    padding-left: 30px;
    padding-right: 30px;
}

.episode-item:last-child {
    border-bottom: none;
}

.episode-number {
    width: 60px;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 600;
    color: #999;
    background: #f8f9fa;
    border-radius: 4px;
    padding: 4px 8px;
    text-align: center;
}

.episode-thumbnail {
    width: 60px;
    height: 45px;
    margin: 0 15px;
    border-radius: 4px;
    overflow: hidden;
    background: #f0f0f0;
    flex-shrink: 0;
}

.episode-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.episode-content {
    flex: 1;
    margin-right: 15px;
}

.episode-title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    line-height: 1.3;
}

.episode-meta {
    font-size: 12px;
    color: #999;
    display: flex;
    gap: 12px;
}

.episode-actions {
    display: flex;
    gap: 8px;
}

.episode-action {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    color: #666;
    text-decoration: none;
}

.episode-action:hover {
    background: #e9ecef;
}

/* 갤러리 그리드 (기존 리스트 뷰용) */
.gallery-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.board-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.board-title {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
}

.board-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-section {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.action-section {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
}

.btn-primary {
    background: #333;
    color: white;
    border-color: #333;
}

.btn-primary:hover {
    background: #555;
}

.btn-bulk {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.btn-bulk:hover {
    background: #218838;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, 470px);
    gap: 20px;
    justify-content: center;
    margin-bottom: 40px;
}

.work-card {
    width: 470px;
    height: 250px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: flex;
    transition: all 0.3s ease;
    cursor: pointer;
}

.work-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.card-thumbnail {
    width: 180px;
    height: 250px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.card-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-content {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
    line-height: 1.2;
}

.card-subtitle {
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
}

.card-description {
    font-size: 13px;
    color: #555;
    line-height: 1.4;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 15px;
}

.tag {
    background: #f1f3f4;
    color: #5f6368;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #999;
}

.view-count {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* 반응형 */
@media (max-width: 768px) {
    .series-container, .gallery-container {
        padding: 15px;
    }
    
    .series-banner {
        height: 200px;
    }
    
    .series-info {
        padding: 20px;
    }
    
    .series-title-area h1 {
        font-size: 24px;
    }
    
    .series-meta-top {
        flex-direction: column;
        gap: 15px;
    }
    
    .series-actions {
        justify-content: flex-start;
    }
    
    .posts-section {
        padding: 20px;
    }
    
    .episode-item {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .episode-thumbnail {
        width: 50px;
        height: 38px;
        margin: 0 10px 0 0;
    }
    
    .board-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-section, .action-section {
        justify-content: center;
    }
    
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .work-card {
        width: 100%;
        height: 200px;
    }
    
    .card-thumbnail {
        width: 130px;
        height: 200px;
    }
}
</style>

<div class="<?php echo $is_series_view ? 'series-container' : 'gallery-container' ?>">
    <?php if($is_series_view && $series_info) { ?>
    <!-- 시리즈 상세 뷰 -->
    <div class="back-nav">
        <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>" class="back-btn">
            ← 목록
        </a>
    </div>

    <!-- 시리즈 헤더 -->
    <div class="series-header">
        <div class="series-banner">
            <?php if($series_thumb) { ?>
                <img src="<?php echo $series_thumb ?>" alt="<?php echo $sca ?>">
            <?php } ?>
        </div>
        
        <div class="series-info">
            <div class="series-meta-top">
                <div class="series-title-area">
                    <h1><?php echo $sca ?></h1>
                    <div class="series-subtitle">
                        작가: <?php echo $series_info['wr_name'] ?> • 
                        <?php echo date('Y.m.d', strtotime($series_info['wr_datetime'])) ?> • 
                        조회 <?php echo number_format($series_info['wr_hit']) ?>
                        <span class="update-badge">연재중</span>
                    </div>
                </div>
                
                <div class="series-actions">
                    <?php if ($is_member && $member['mb_level'] >= $board['bo_write_min']) { ?>
                    <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=<?php echo $bo_table ?>&ca_name=<?php echo urlencode($sca) ?>" class="action-btn primary">
                        ✏️ 포스트 작성
                    </a>
                    <a href="<?php echo G5_URL ?>/bulk_upload.php?bo_table=<?php echo $bo_table ?>&category=<?php echo urlencode($sca) ?>" class="action-btn bulk-upload">
                        📁 일괄 업로드
                    </a>
                    <?php } ?>
                    
                    <?php if ($is_admin || $member['mb_id'] === $series_info['mb_id']) { ?>
                    <button class="action-btn" onclick="editSeries('<?php echo $sca ?>')">
                        🛠️ 시리즈 수정
                    </button>
                    <button class="action-btn" onclick="deleteSeries('<?php echo $sca ?>')">
                        🗑️ 시리즈 삭제
                    </button>
                    <?php } ?>
                </div>
            </div>
            
            <?php 
            // 시리즈 설명 (첫 번째 글의 내용에서 추출)
            $description = strip_tags($series_info['wr_content']);
            $description = preg_replace('/#[^\s#]+/', '', $description); // 태그 제거
            $description = cut_str(trim($description), 200, '...');
            
            // 태그 추출
            preg_match_all('/#([^\s#]+)/', $series_info['wr_content'], $tag_matches);
            $tags = array_slice($tag_matches[1], 0, 8);
            ?>
            
            <?php if($description) { ?>
            <div class="series-description"><?php echo $description ?></div>
            <?php } ?>
            
            <?php if(!empty($tags)) { ?>
            <div class="series-tags">
                <?php foreach($tags as $tag) { ?>
                    <span class="series-tag">#<?php echo $tag ?></span>
                <?php } ?>
            </div>
            <?php } ?>
            
            <div class="series-stats">
                <span>🗂️ <?php echo count($list) ?>개의 포스트</span>
                <span>👁️ 총 조회수 <?php echo number_format(array_sum(array_column($list, 'wr_hit'))) ?></span>
                <span>📅 마지막 업데이트 <?php echo date('Y.m.d', strtotime($series_info['wr_datetime'])) ?></span>
            </div>
        </div>
    </div>

    <!-- 포스트 목록 섹션 -->
    <div class="posts-section">
        <div class="posts-header">
            <h2 class="posts-title">포스트 목록</h2>
            <div class="posts-controls">
                <button class="sort-btn active" onclick="sortEpisodes('first')">첫 화</button>
                <button class="sort-btn" onclick="sortEpisodes('latest')">최신 화</button>
            </div>
        </div>

        <div class="episode-list" id="episodeList">
            <?php 
            // 에피소드 번호를 위한 카운터
            $episode_num = 1;
            foreach($list as $i => $episode) {
                // 에피소드 썸네일
                $episode_thumb = '';
                $file_sql = "SELECT bf_file FROM {$g5['board_file_table']} 
                           WHERE bo_table = '{$bo_table}' AND wr_id = '{$episode['wr_id']}' 
                           AND bf_file != '' ORDER BY bf_no LIMIT 1";
                $file_result = sql_fetch($file_sql);
                if($file_result) {
                    $episode_thumb = G5_DATA_URL.'/file/'.$bo_table.'/'.$file_result['bf_file'];
                }
            ?>
            <div class="episode-item" onclick="location.href='<?php echo $episode['href'] ?>'">
                <div class="episode-number"><?php echo sprintf('%03d', $episode_num) ?></div>
                
                <div class="episode-thumbnail">
                    <?php if($episode_thumb) { ?>
                        <img src="<?php echo $episode_thumb ?>" alt="<?php echo $episode['subject'] ?>">
                    <?php } else { ?>
                        <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #ccc;">📄</div>
                    <?php } ?>
                </div>
                
                <div class="episode-content">
                    <div class="episode-title"><?php echo $episode['subject'] ?></div>
                    <div class="episode-meta">
                        <span>작가: <?php echo $episode['wr_name'] ?></span>
                        <span><?php echo date('Y.m.d', strtotime($episode['wr_datetime'])) ?></span>
                        <span>조회 <?php echo number_format($episode['wr_hit']) ?></span>
                    </div>
                </div>
                
                <?php if ($is_admin || $member['mb_id'] === $episode['mb_id']) { ?>
                <div class="episode-actions">
                    <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $episode['wr_id'] ?>" class="episode-action" onclick="event.stopPropagation()">수정</a>
                    <a href="<?php echo G5_BBS_URL ?>/delete.php?bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $episode['wr_id'] ?>" class="episode-action" onclick="event.stopPropagation()" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                </div>
                <?php } ?>
            </div>
            <?php 
                $episode_num++;
            } 
            ?>
        </div>
    </div>

    <?php } else { ?>
    <!-- 기존 갤러리 뷰 -->
    <div class="board-header">
        <h1 class="board-title"><?php echo $board['bo_subject'] ?></h1>
        
        <div class="board-controls">
            <div class="filter-section">
                <select class="btn" onchange="filterByCategory(this.value)">
                    <option value="">전체 카테고리</option>
                    <?php
                    // 카테고리 목록 가져오기
                    $cat_sql = "SELECT DISTINCT ca_name FROM {$g5['board_table']} 
                               WHERE bo_table = '{$bo_table}' AND ca_name != '' 
                               ORDER BY ca_name";
                    $cat_result = sql_query($cat_sql);
                    while($cat = sql_fetch_array($cat_result)) {
                        $selected = ($sca == $cat['ca_name']) ? 'selected' : '';
                        echo "<option value='{$cat['ca_name']}' {$selected}>{$cat['ca_name']}</option>";
                    }
                    ?>
                </select>
                
                <form name="fsearch" method="get" class="search-form" style="display: inline-flex; gap: 10px;">
                    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
                    <input type="hidden" name="sca" value="<?php echo $sca ?>">
                    <input type="text" name="stx" value="<?php echo $stx ?>" placeholder="검색어 입력" class="btn" style="width: 200px;">
                    <button type="submit" class="btn">🔍 검색</button>
                </form>
            </div>
            
            <div class="action-section">
                <?php if ($is_member && $member['mb_level'] >= $board['bo_write_min']) { ?>
                <a href="<?php echo G5_URL ?>/bulk_upload.php?bo_table=<?php echo $bo_table ?>" class="btn btn-bulk">
                    📁 일괄 업로드
                </a>
                <a href="<?php echo $write_href ?>" class="btn btn-primary">
                    ✏️ 글쓰기
                </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="gallery-grid">
        <?php
        // 시리즈별로 그룹화
        $series_groups = array();
        foreach($list as $item) {
            $ca_name = $item['ca_name'] ?: '일반';
            if(!isset($series_groups[$ca_name])) {
                $series_groups[$ca_name] = array();
            }
            $series_groups[$ca_name][] = $item;
        }
        
        // 각 시리즈를 카드로 표시
        foreach($series_groups as $series_name => $items) {
            $latest_item = $items[0]; // 최신 글
            
            // 시리즈 썸네일
            $series_thumb = '';
            $file_sql = "SELECT bf_file FROM {$g5['board_file_table']} 
                        WHERE bo_table = '{$bo_table}' AND wr_id = '{$latest_item['wr_id']}' 
                        AND bf_file != '' ORDER BY bf_no LIMIT 1";
            $file_result = sql_fetch($file_sql);
            if($file_result) {
                $series_thumb = G5_DATA_URL.'/file/'.$bo_table.'/'.$file_result['bf_file'];
            }
            
            // 시리즈 설명
            $series_desc = strip_tags($latest_item['wr_content']);
            $series_desc = preg_replace('/#[^\s#]+/', '', $series_desc);
            $series_desc = cut_str(trim($series_desc), 100, '...');
            
            // 태그 추출
            preg_match_all('/#([^\s#]+)/', $latest_item['wr_content'], $tag_matches);
            $tags = array_slice($tag_matches[1], 0, 5);
        ?>
        
        <div class="work-card" onclick="location.href='<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>&sca=<?php echo urlencode($series_name) ?>'">
            <div class="card-thumbnail">
                <?php if($series_thumb) { ?>
                    <img src="<?php echo $series_thumb ?>" alt="<?php echo $series_name ?>">
                <?php } else { ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 48px; color: rgba(255,255,255,0.7);">📁</div>
                <?php } ?>
            </div>
            
            <div class="card-content">
                <div class="card-header">
                    <div class="card-title"><?php echo $series_name ?></div>
                    <div class="card-subtitle">
                        <?php echo $latest_item['wr_name'] ?> • <?php echo count($items) ?>편
                    </div>
                </div>
                
                <?php if($series_desc) { ?>
                <div class="card-description"><?php echo $series_desc ?></div>
                <?php } ?>
                
                <?php if(!empty($tags)) { ?>
                <div class="card-tags">
                    <?php foreach($tags as $tag) { ?>
                        <span class="tag">#<?php echo $tag ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
                
                <div class="card-meta">
                    <span><?php echo date('Y.m.d', strtotime($latest_item['wr_datetime'])) ?></span>
                    <div class="view-count">
                        <span>👁</span>
                        <span><?php echo number_format(array_sum(array_column($items, 'wr_hit'))) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } ?>
        
        <?php if (count($series_groups) == 0) { ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #666;">
            <p style="font-size: 16px; margin-bottom: 20px;">아직 게시물이 없습니다.</p>
            <?php if ($is_member && $member['mb_level'] >= $board['bo_write_min']) { ?>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo $write_href ?>" class="btn btn-primary">첫 번째 작품 업로드하기</a>
                <a href="<?php echo G5_URL ?>/bulk_upload.php?bo_table=<?php echo $bo_table ?>" class="btn btn-bulk">일괄 업로드로 시작하기</a>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 페이지네이션 -->
    <?php if($write_pages) { ?>
    <div style="text-align: center; margin: 40px 0;">
        <?php echo $write_pages ?>
    </div>
    <?php } ?>
</div>

<script>
// 카테고리 필터
function filterByCategory(category) {
    const url = new URL(window.location);
    if(category) {
        url.searchParams.set('sca', category);
    } else {
        url.searchParams.delete('sca');
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// 에피소드 정렬
function sortEpisodes(type) {
    const episodeList = document.getElementById('episodeList');
    if (!episodeList) return;
    
    // 정렬 버튼 상태 업데이트
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    const episodes = Array.from(episodeList.children);
    
    if(type === 'latest') {
        episodes.reverse();
    }
    
    episodeList.innerHTML = '';
    episodes.forEach(episode => episodeList.appendChild(episode));
}

// 시리즈 관리 함수들
function editSeries(seriesName) {
    if(confirm(`"${seriesName}" 시리즈를 수정하시겠습니까?`)) {
        // 시리즈 수정 페이지로 이동 (구현 필요)
        alert('시리즈 수정 기능은 곧 추가될 예정입니다.');
    }
}

function deleteSeries(seriesName) {
    if(confirm(`"${seriesName}" 시리즈를 정말 삭제하시겠습니까?\n\n⚠️ 주의: 시리즈 내 모든 포스트가 함께 삭제됩니다!`)) {
        if(confirm('정말로 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
            // 시리즈 삭제 처리 (구현 필요)
            alert('시리즈 삭제 기능은 곧 추가될 예정입니다.');
        }
    }
}

// 카드 호버 효과
document.querySelectorAll('.work-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// 태그 클릭으로 검색
document.querySelectorAll('.tag').forEach(tag => {
    tag.addEventListener('click', function(e) {
        e.stopPropagation();
        const tagText = this.textContent;
        const searchForm = document.querySelector('input[name="stx"]');
        if(searchForm) {
            searchForm.value = tagText;
            searchForm.closest('form').submit();
        }
    });
});

// 일괄 업로드 버튼 강조 효과
document.querySelectorAll('.btn-bulk, .action-btn.bulk-upload').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 12px rgba(40, 167, 69, 0.3)';
    });
    
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
    });
});
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
