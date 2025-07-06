<?php
// my-skins/gallery-style/list.php
// 아보카도 에디션 카드 갤러리 스킨

if (!defined('_GNUBOARD_')) exit;

// 게시판 설정
$g5['title'] = $board['bo_subject'];
include_once(G5_PATH.'/head.php');
?>

<style>
/* 카드 갤러리 스타일 */
.gallery-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.board-header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.board-title {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}

.board-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-section {
    display: flex;
    gap: 10px;
    align-items: center;
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
}

.btn:hover {
    background: #f8f9fa;
}

.btn-primary {
    background: #333;
    color: white;
    border-color: #333;
}

/* 갤러리 그리드 */
.works-grid {
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
@media (max-width: 1000px) {
    .works-grid {
        grid-template-columns: repeat(auto-fill, 420px);
    }
    .work-card {
        width: 420px;
        height: 220px;
    }
    .card-thumbnail {
        width: 150px;
        height: 220px;
    }
}

@media (max-width: 768px) {
    .works-grid {
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

<div class="gallery-container">
    <!-- 게시판 헤더 -->
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
                    <button type="submit" class="btn">검색</button>
                </form>
            </div>
            
            <?php if ($is_member) { ?>
            <a href="<?php echo $write_href ?>" class="btn-primary">글쓰기</a>
            <?php } ?>
        </div>
    </div>

    <!-- 작품 그리드 -->
    <div class="works-grid">
        <?php
        for ($i=0; $i<count($list); $i++) {
            $wr_id = $list[$i]['wr_id'];
            
            // 첫 번째 이미지 파일 가져오기
            $file_sql = "SELECT bf_file, bf_content FROM {$g5['board_file_table']} 
                        WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' 
                        AND bf_file != '' ORDER BY bf_no LIMIT 1";
            $file = sql_fetch($file_sql);
            
            // 썸네일 경로
            $thumb_path = '';
            if($file && $file['bf_file']) {
                $thumb_path = G5_DATA_URL.'/file/'.$bo_table.'/'.$file['bf_file'];
            }
            
            // 내용에서 태그 추출 (예: #태그1 #태그2)
            $content = strip_tags($list[$i]['wr_content']);
            preg_match_all('/#([^\s#]+)/', $content, $tag_matches);
            $tags = array_slice($tag_matches[1], 0, 5); // 최대 5개 태그
            
            // 설명 텍스트 (태그 제거 후)
            $description = preg_replace('/#[^\s#]+/', '', $content);
            $description = cut_str(trim($description), 120, '...');
        ?>
        
        <div class="work-card" onclick="location.href='<?php echo $list[$i]['href'] ?>'">
            <div class="card-thumbnail">
                <?php if($thumb_path) { ?>
                    <img src="<?php echo $thumb_path ?>" alt="<?php echo $list[$i]['subject'] ?>">
                <?php } else { ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 48px; color: rgba(255,255,255,0.7);">
                        📄
                    </div>
                <?php } ?>
            </div>
            
            <div class="card-content">
                <div class="card-header">
                    <div class="card-title"><?php echo $list[$i]['subject'] ?></div>
                    <div class="card-subtitle">
                        <?php echo $list[$i]['name'] ?> • <?php echo date('Y.m.d', strtotime($list[$i]['datetime'])) ?>
                    </div>
                </div>
                
                <?php if($description) { ?>
                <div class="card-description"><?php echo $description ?></div>
                <?php } ?>
                
                <?php if(!empty($tags)) { ?>
                <div class="card-tags">
                    <?php foreach($tags as $tag) { ?>
                        <span class="tag">#<?php echo $tag ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
                
                <div class="card-meta">
                    <span><?php echo $list[$i]['ca_name'] ?: '일반' ?></span>
                    <div class="view-count">
                        <span>👁</span>
                        <span><?php echo number_format($list[$i]['wr_hit']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } ?>
        
        <?php if (count($list) == 0) { ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #666;">
            <p style="font-size: 16px; margin-bottom: 20px;">아직 게시물이 없습니다.</p>
            <?php if ($is_member) { ?>
            <a href="<?php echo $write_href ?>" class="btn-primary">첫 번째 작품 업로드하기</a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php echo $write_pages ?>
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
        searchForm.value = tagText;
        searchForm.closest('form').submit();
    });
});
</script>

<?php include_once(G5_PATH.'/tail.php'); ?>
