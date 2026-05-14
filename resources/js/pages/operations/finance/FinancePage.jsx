import React, { useEffect, useState } from 'react';
import api from '../../../services/api';

const today = () => new Date().toISOString().slice(0, 10);

const money = (value) => {
  const number = Number(value || 0);
  return `${number.toLocaleString('vi-VN')} VNĐ`;
};

const unwrapList = (response) => {
  const data = response.data.data || response.data || [];
  return Array.isArray(data) ? data : (data.data || []);
};

const FinancePage = () => {
  const [activeTab, setActiveTab] = useState('summary');
  const [prices, setPrices] = useState([]);
  const [breakdowns, setBreakdowns] = useState([]);
  const [costSummary, setCostSummary] = useState(null);
  const [marginSummary, setMarginSummary] = useState(null);
  const [farms, setFarms] = useState([]);
  const [crops, setCrops] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [priceForm, setPriceForm] = useState({
    farm_id: '',
    crop_id: '',
    unit: 'kg',
    grade_a_price: '',
    grade_b_price: '',
    grade_c_price: '',
    side_channel_price: '',
    effective_from: today(),
    notes: '',
  });

  const loadData = async () => {
    setLoading(true);
    setError(null);

    try {
      const [pricesRes, breakdownsRes, marginRes, farmsRes, cropsRes] = await Promise.all([
        api.get('/price-tables'),
        api.get('/cost-breakdowns'),
        api.get('/margin-dashboard'),
        api.get('/farms'),
        api.get('/crops'),
      ]);
      setPrices(unwrapList(pricesRes));
      setBreakdowns(unwrapList(breakdownsRes));
      setMarginSummary(marginRes.data.data || marginRes.data || null);
      setFarms(unwrapList(farmsRes));
      setCrops(unwrapList(cropsRes));

      try {
        const costRes = await api.get('/cost-breakdowns/dashboard');
        setCostSummary(costRes.data.data || costRes.data || null);
      } catch (summaryError) {
        setCostSummary(null);
      }
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải dữ liệu tài chính.');
      console.error('Error loading finance data:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const submitPrice = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const payload = {
        ...priceForm,
        grade_a_price: parseFloat(priceForm.grade_a_price),
        grade_b_price: priceForm.grade_b_price ? parseFloat(priceForm.grade_b_price) : undefined,
        grade_c_price: priceForm.grade_c_price ? parseFloat(priceForm.grade_c_price) : undefined,
        side_channel_price: priceForm.side_channel_price ? parseFloat(priceForm.side_channel_price) : undefined,
        status: 'active',
      };

      Object.keys(payload).forEach((key) => {
        if (payload[key] === '' || payload[key] === undefined || payload[key] === null) delete payload[key];
      });

      const response = await api.post('/price-tables', payload);
      setPrices((prev) => [response.data.data, ...prev]);
      setPriceForm({
        farm_id: '',
        crop_id: '',
        unit: 'kg',
        grade_a_price: '',
        grade_b_price: '',
        grade_c_price: '',
        side_channel_price: '',
        effective_from: today(),
        notes: '',
      });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể lưu bảng giá.');
      console.error('Error saving price table:', err);
    } finally {
      setSaving(false);
    }
  };

  const summaryCards = [
    ['Doanh thu ước tính', marginSummary?.estimated_revenue ?? marginSummary?.total_estimated_revenue],
    ['Doanh thu thực tế', marginSummary?.actual_revenue ?? marginSummary?.total_actual_revenue],
    ['Chi phí', marginSummary?.estimated_cost ?? marginSummary?.total_cost],
    ['Lợi nhuận', marginSummary?.estimated_margin ?? marginSummary?.gross_margin],
  ];

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-gray-800">Tài chính và biên lợi nhuận</h1>
        <p className="text-gray-600">Theo dõi giá bán, chi phí, doanh thu và hiệu quả theo kế hoạch/lứa trồng.</p>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>}

      <div className="bg-white rounded-lg shadow border border-gray-200">
        <div className="border-b border-gray-200 p-2 flex gap-2 overflow-x-auto">
          {[
            ['summary', 'Tổng quan'],
            ['prices', 'Bảng giá'],
            ['costs', 'Chi phí'],
          ].map(([id, label]) => (
            <button key={id} type="button" onClick={() => setActiveTab(id)} className={`px-4 py-2 rounded-lg text-sm font-medium ${activeTab === id ? 'bg-emerald-100 text-emerald-800' : 'text-gray-600 hover:bg-gray-100'}`}>
              {label}
            </button>
          ))}
        </div>

        {loading ? (
          <div className="p-8 text-center text-gray-600">Đang tải dữ liệu tài chính...</div>
        ) : (
          <div className="p-4">
            {activeTab === 'summary' && (
              <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                  {summaryCards.map(([label, value]) => (
                    <div key={label} className="border border-gray-200 rounded-lg p-4">
                      <div className="text-xs text-gray-500">{label}</div>
                      <div className="mt-1 text-xl font-semibold text-gray-800">{money(value)}</div>
                    </div>
                  ))}
                </div>
                {costSummary && (
                  <div className="border border-gray-200 rounded-lg p-4">
                    <h2 className="font-medium text-gray-800 mb-3">Tóm tắt chi phí</h2>
                    <pre className="text-xs bg-gray-50 p-3 rounded-lg overflow-auto">{JSON.stringify(costSummary, null, 2)}</pre>
                  </div>
                )}
              </div>
            )}

            {activeTab === 'prices' && (
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <form onSubmit={submitPrice} className="space-y-3">
                  <h2 className="font-medium text-gray-800">Thêm bảng giá</h2>
                  <select value={priceForm.farm_id} onChange={(e) => setPriceForm((p) => ({ ...p, farm_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Giá chung hoặc farm của tài khoản</option>
                    {farms.map((farm) => <option key={farm.id} value={farm.id}>{farm.name}</option>)}
                  </select>
                  <select value={priceForm.crop_id} onChange={(e) => setPriceForm((p) => ({ ...p, crop_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white" required>
                    <option value="">Chọn cây</option>
                    {crops.map((crop) => <option key={crop.id} value={crop.id}>{crop.name}</option>)}
                  </select>
                  <div className="grid grid-cols-2 gap-2">
                    <select value={priceForm.unit} onChange={(e) => setPriceForm((p) => ({ ...p, unit: e.target.value }))} className="px-3 py-2 border rounded-lg bg-white">
                      <option value="kg">kg</option><option value="trái">trái</option><option value="bó">bó</option><option value="thùng">thùng</option>
                    </select>
                    <input type="date" value={priceForm.effective_from} onChange={(e) => setPriceForm((p) => ({ ...p, effective_from: e.target.value }))} className="px-3 py-2 border rounded-lg" required />
                  </div>
                  <input type="number" step="any" min="0" value={priceForm.grade_a_price} onChange={(e) => setPriceForm((p) => ({ ...p, grade_a_price: e.target.value }))} placeholder="Giá loại A" className="w-full px-3 py-2 border rounded-lg" required />
                  <div className="grid grid-cols-3 gap-2">
                    <input type="number" step="any" min="0" value={priceForm.grade_b_price} onChange={(e) => setPriceForm((p) => ({ ...p, grade_b_price: e.target.value }))} placeholder="Loại B" className="px-3 py-2 border rounded-lg" />
                    <input type="number" step="any" min="0" value={priceForm.grade_c_price} onChange={(e) => setPriceForm((p) => ({ ...p, grade_c_price: e.target.value }))} placeholder="Loại C" className="px-3 py-2 border rounded-lg" />
                    <input type="number" step="any" min="0" value={priceForm.side_channel_price} onChange={(e) => setPriceForm((p) => ({ ...p, side_channel_price: e.target.value }))} placeholder="Kênh phụ" className="px-3 py-2 border rounded-lg" />
                  </div>
                  <button disabled={saving} className="px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50">{saving ? 'Đang lưu...' : 'Lưu giá'}</button>
                </form>
                <div className="lg:col-span-2 overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Cây</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Đơn vị</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Giá A</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Hiệu lực</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Trạng thái</th></tr></thead>
                    <tbody className="divide-y divide-gray-200">
                      {prices.map((price) => <tr key={price.id}><td className="px-3 py-2">{price.crop?.name || '-'}</td><td className="px-3 py-2">{price.unit}</td><td className="px-3 py-2">{money(price.grade_a_price)}</td><td className="px-3 py-2">{price.effective_from}</td><td className="px-3 py-2">{price.status || '-'}</td></tr>)}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {activeTab === 'costs' && (
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Loại</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Kế hoạch/Lứa</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Chi phí</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Sản lượng</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Cost/kg</th></tr></thead>
                  <tbody className="divide-y divide-gray-200">
                    {breakdowns.map((item) => <tr key={item.id}><td className="px-3 py-2">{item.breakdown_type}</td><td className="px-3 py-2">{item.production_plan_id ? `Plan #${item.production_plan_id}` : item.planting_batch_id ? `Lứa #${item.planting_batch_id}` : item.farm?.name || '-'}</td><td className="px-3 py-2">{money(item.total_cost)}</td><td className="px-3 py-2">{item.total_yield_kg || 0} kg</td><td className="px-3 py-2">{money(item.cost_per_kg)}</td></tr>)}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default FinancePage;

