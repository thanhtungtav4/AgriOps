import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import type { WorkTask } from '../types';

interface Props {
  task: WorkTask;
  onPress: (task: WorkTask) => void;
}

const statusColors: Record<string, string> = {
  planned: '#6b7280',
  assigned: '#3b82f6',
  in_progress: '#f59e0b',
  done: '#16a34a',
  cancelled: '#dc2626',
};

const statusLabels: Record<string, string> = {
  planned: 'Kế hoạch',
  assigned: 'Đã nhận',
  in_progress: 'Đang làm',
  done: 'Hoàn thành',
  cancelled: 'Hủy',
};

const taskTitle = (task: WorkTask) => task.title || `Việc #${task.id}`;
const taskBatch = (task: WorkTask) => task.planting_batch;

export function TaskCard({ task, onPress }: Props) {
  const batch = taskBatch(task);

  return (
    <TouchableOpacity style={styles.card} onPress={() => onPress(task)} activeOpacity={0.7}>
      <View style={styles.header}>
        <View style={[styles.statusBadge, { backgroundColor: statusColors[task.status] || '#6b7280' }]}>
          <Text style={styles.statusText}>{statusLabels[task.status] || task.status}</Text>
        </View>
        {(task.priority === 'high' || task.priority === 'urgent') && (
          <Text style={styles.highPriority}>Khẩn</Text>
        )}
      </View>

      <Text style={styles.title}>{taskTitle(task)}</Text>

      {batch?.crop && (
        <Text style={styles.context}>{batch.crop.name}</Text>
      )}

      {task.plot && (
        <Text style={styles.context}>{task.plot.name}</Text>
      )}

      <Text style={styles.due}>
        Hạn: {task.planned_due_date ? new Date(task.planned_due_date).toLocaleDateString('vi-VN') : 'Không có'}
      </Text>

      {task.metadata?.requires_photo && (
        <Text style={styles.photoRequired}>Cần chụp ảnh</Text>
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  statusText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '600',
  },
  highPriority: {
    marginLeft: 8,
    color: '#dc2626',
    fontSize: 12,
    fontWeight: '600',
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1f2937',
    marginBottom: 4,
  },
  context: {
    fontSize: 14,
    color: '#6b7280',
    marginBottom: 2,
  },
  due: {
    fontSize: 13,
    color: '#9ca3af',
    marginTop: 4,
  },
  photoRequired: {
    fontSize: 13,
    color: '#f59e0b',
    marginTop: 4,
    fontWeight: '500',
  },
});
