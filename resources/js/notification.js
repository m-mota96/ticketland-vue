import { ElNotification } from 'element-plus';

export const showNotification = (title, message, type) => {
    ElNotification({
        title,
        dangerouslyUseHTMLString: true,
        message,
        type,
    });
};