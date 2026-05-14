import React, { useEffect, useState } from 'react';
import api from '../../../services/api';

const unwrap = (response) => {
  const data = response.data.data || response.data || [];
  return Array.isArray(data) ? data : (data.data || []);
};

const PostSeasonReviewsPage = () => {
  const [reviews, setReviews] = useState([]);
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [form, setForm] = useState({
    production_plan_id: '',
    actual_performance_summary: '',
    yield_analysis: '',
    quality_assessment: '',
    resource_utilization_review: '',
    pest_disease_review: '',
    weather_impact_analysis: '',
    lessons_learned: '',
    recommendations: '',
    next_season_improvements: '',
  });

  const loadData = async () => {
    setLoading(true);
    setError(null);

    try {
      const [reviewsRes, plansRes] = await Promise.all([
        api.get('/post-season-reviews'),
        api.get('/production-plans'),
      ]);
      setReviews(unwrap(reviewsRes));
      setPlans(unwrap(plansRes));
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải báo cáo sau vụ.');
      console.error('Error loading post-season reviews:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const submitReview = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const payload = { ...form };
      Object.keys(payload).forEach((key) => {
        if (payload[key] === '' || payload[key] === undefined || payload[key] === null) delete payload[key];
      });
      const response = await api.post('/post-season-reviews', payload);
      setReviews((prev) => [response.data.data, ...prev]);
      setForm({
        production_plan_id: '',
        actual_performance_summary: '',
        yield_analysis: '',
        quality_assessment: '',
        resource_utilization_review: '',
        pest_disease_review: '',
        weather_impact_analysis: '',
        lessons_learned: '',
        recommendations: '',
        next_season_improvements: '',
      });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tạo báo cáo sau vụ.');
      console.error('Error creating post-season review:', err);
    } finally {
      setSaving(false);
    }
  };

  const runAction = async (review, action) => {
    setSaving(true);
    setError(null);

    try {
      const payload = action === 'reject'
        ? { reason: window.prompt('Lý do từ chối?') || 'Không đạt yêu cầu duyệt.' }
        : {};
      const response = await api.post(`/post-season-reviews/${review.id}/${action}`, payload);
      setReviews((prev) => prev.map((item) => item.id === review.id ? response.data.data : item));
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể cập nhật trạng thái báo cáo.');
      console.error('Error updating post-season review:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-gray-800">Cải tiến sau vụ</h1>
        <p className="text-gray-600">Ghi nhận thực tế, bài học và đề xuất cải tiến định mức cho vụ sau.</p>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form onSubmit={submitReview} className="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3">
          <h2 className="font-medium text-gray-800">Tạo báo cáo sau vụ</h2>
          <select value={form.production_plan_id} onChange={(e) => setForm((p) => ({ ...p, production_plan_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white" required>
            <option value="">Chọn kế hoạch sản xuất</option>
            {plans.map((plan) => <option key={plan.id} value={plan.id}>#{plan.id} - {plan.crop?.name || 'cây trồng'} - {plan.target_delivery_date}</option>)}
          </select>
          <textarea value={form.actual_performance_summary} onChange={(e) => setForm((p) => ({ ...p, actual_performance_summary: e.target.value }))} rows="3" placeholder="Tổng kết thực tế..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.yield_analysis} onChange={(e) => setForm((p) => ({ ...p, yield_analysis: e.target.value }))} rows="3" placeholder="Phân tích năng suất..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.quality_assessment} onChange={(e) => setForm((p) => ({ ...p, quality_assessment: e.target.value }))} rows="3" placeholder="Đánh giá chất lượng..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.resource_utilization_review} onChange={(e) => setForm((p) => ({ ...p, resource_utilization_review: e.target.value }))} rows="3" placeholder="Nước, nhân công, vật tư..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.pest_disease_review} onChange={(e) => setForm((p) => ({ ...p, pest_disease_review: e.target.value }))} rows="3" placeholder="Sâu bệnh..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.lessons_learned} onChange={(e) => setForm((p) => ({ ...p, lessons_learned: e.target.value }))} rows="3" placeholder="Bài học..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.recommendations} onChange={(e) => setForm((p) => ({ ...p, recommendations: e.target.value }))} rows="3" placeholder="Khuyến nghị..." className="w-full px-3 py-2 border rounded-lg" />
          <textarea value={form.next_season_improvements} onChange={(e) => setForm((p) => ({ ...p, next_season_improvements: e.target.value }))} rows="3" placeholder="Đề xuất điều chỉnh vụ sau..." className="w-full px-3 py-2 border rounded-lg" />
          <button disabled={saving} className="px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50">{saving ? 'Đang lưu...' : 'Lưu nháp'}</button>
        </form>

        <div className="lg:col-span-2 bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-600">Đang tải báo cáo...</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Kế hoạch</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Trạng thái</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Tóm tắt</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Thao tác</th></tr></thead>
                <tbody className="divide-y divide-gray-200">
                  {reviews.map((review) => (
                    <tr key={review.id}>
                      <td className="px-3 py-2">Plan #{review.production_plan_id}</td>
                      <td className="px-3 py-2">{review.status}</td>
                      <td className="px-3 py-2 max-w-md truncate">{review.actual_performance_summary || review.recommendations || '-'}</td>
                      <td className="px-3 py-2">
                        <div className="flex gap-2">
                          {review.status === 'draft' && <button disabled={saving} onClick={() => runAction(review, 'submit')} className="text-blue-700 hover:text-blue-900 text-sm">Gửi duyệt</button>}
                          {review.status === 'submitted' && <button disabled={saving} onClick={() => runAction(review, 'approve')} className="text-emerald-700 hover:text-emerald-900 text-sm">Duyệt</button>}
                          {review.status === 'submitted' && <button disabled={saving} onClick={() => runAction(review, 'reject')} className="text-red-700 hover:text-red-900 text-sm">Từ chối</button>}
                        </div>
                      </td>
                    </tr>
                  ))}
                  {reviews.length === 0 && <tr><td colSpan="4" className="px-3 py-8 text-center text-gray-500">Chưa có báo cáo sau vụ.</td></tr>}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default PostSeasonReviewsPage;

