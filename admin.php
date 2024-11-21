<?php
// 그리드 형태 포스트 목록 숏코드 함수
function grid_posts_shortcode($atts) {
	$current_user = wp_get_current_user();
	if(in_array( 'administrator', (array) $current_user->roles )) {
		// 기본 속성 설정 (포스트 수와 카테고리 지정 가능)
		$atts = shortcode_atts(array(
			'posts_per_page' => 6,
			'category' => 'image-post',
		), $atts);
	
		// WP_Query 설정
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => intval($atts['posts_per_page']),
			'category_name' => sanitize_text_field($atts['category']),
		);

        if(isset($_POST['submit_name'])) {
            $args1 = array(
                'meta_key' => 'product_name',
                'meta_value' => $_POST['product_name'],
                'meta_compare' => 'LIKE'
            );
            $args = array_merge($args, $args1);
        }
	
		$query = new WP_Query($args);
		$output = '<div class="grid-posts-container" style="display: grid; grid-template-columns: repeat(3, minmax(200px, 1fr)); gap: 20px;">';
        $output .= '<form method="post">
            <input type="text" name="product_name">
            <input type="submit" name="submit_name" value="Search">
            </form>';
	
		// 포스트 출력
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$output .= '<div class="grid-post-item" style="border: 1px solid #ddd; padding: 10px; text-align: center;">';
				$output .= '<a href="' . get_permalink() . '" style="text-decoration: none; color: inherit;">';
				
				// 썸네일 추가
				if (has_post_thumbnail()) {
					$output .= get_the_post_thumbnail(get_the_ID(), 'medium', array('style' => 'width: 100%; height: auto;'));
				}
	
				// 제목과 내용 추가
				$output .= '<h3 style="font-size: 1.2em; margin-top: 10px;">' . get_the_title() . '</h3>';
				$output .= '<p style="font-size: 0.9em; color: #555;">' . wp_trim_words(get_the_excerpt(), 15) . '</p>';
				$output .= '</a>';
				$output .= '<a href="/wordpress/?page_id=64&pid=' . get_the_ID() . '">수정</a>';
				$output .= '<a href="/wordpress/?page_id=237&pid=' . get_the_ID() . '" onclick="return confirm(\'삭제하시겠습니까?\');">삭제</a></div>';
			}
		} else {
			$output .= '<p>No posts found.</p>';
		}
	
		$output .= '</div>';
		wp_reset_postdata(); // 쿼리 리셋
		return $output;
	} else {
		return '<script>alert("권한이 없습니다."); history.back();</script>';
	}
}
// 숏코드 등록
add_shortcode('grid_posts', 'grid_posts_shortcode');

function display_user_list() {
    $users = get_users();
    $output = '<table>';
    $output .= '<tr><th>이름</th><th>이메일</th><th>역할</th><th>액션</th></tr>';
    $rownum = 0;
    foreach ($users as $user) {
        $output .= '<tr name = ' . $rownum .'>';
        $output .= '<td class="user-name">' . esc_html($user->display_name) . '</td>';
        $output .= '<td class="user-email">' . esc_html($user->user_email) . '</td>';
        $output .= '<td class="user-role">' . esc_html(implode(', ', $user->roles)) . '</td>';
        $output .= '<td><a href="?page_id=269&user_id=' . $user->ID . '">수정</a>';
        $output .= '<a href="?action=delete_user&user_id=' . $user->ID . '" onclick="return confirm(\'정말 삭제하시겠습니까?\')">삭제</a></td>';
        $output .= '</tr>';
    }
    $output .= '</table>';
    return $output;
}
add_shortcode('user_list', 'display_user_list');


function update_user_form() {
    if (isset($_GET['user_id']) && current_user_can('edit_users')) {
        $user_id = intval($_GET['user_id']);
        global $wp_roles;
        $role_list = $wp_roles -> get_names();
        $user = get_userdata($user_id);
        if ($user) {
            if (isset($_POST['update_user'])) {
                $updated_user = wp_update_user([
                    'ID' => $user_id,
                    'display_name' => sanitize_text_field($_POST['display_name']),
                    'user_email' => sanitize_text_field($_POST['user_email']),
                ]);
                $user->set_role(sanitize_text_field($_POST['role']));
                if (is_wp_error($updated_user)) {
                    echo '<p>업데이트 실패: ' . $updated_user->get_error_message() . '</p>';
                } else {
                    echo '<p>회원 정보가 업데이트되었습니다.</p>';
                    wp_redirect(get_permalink(265));
                }
            }
            $upload_form = 
            '<div>
                <form method="post">
                    <label>이름: <input type="text" name="display_name" value="' . esc_attr($user->display_name) . '"></label> <br/>
                    <label>이메일: <input type="text" name="user_email" value="' . esc_attr($user->user_email) . '"></label> <br/>
                    <select name="role">';
                    foreach($role_list as $role) {
                        $role_lowercase = strtolower($role);
                        if(in_array($role_lowercase, $user->roles)) {
                            $upload_form .= '<option value=' . $role_lowercase . ' selected>' . $role_lowercase . '</option>';
                        } else {
                            $upload_form .= '<option value=' . $role_lowercase . '>' . $role_lowercase . '</option>';
                        }
                    }
                    $upload_form .=
                    '</select> <br/>
                    <input type="submit" name="update_user" value="수정">
                </form>
            </div>';
            return $upload_form;
        }
    }
}
add_shortcode('edit_user_form', 'update_user_form');
?>
