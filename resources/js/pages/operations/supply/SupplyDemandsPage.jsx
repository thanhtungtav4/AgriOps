import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../../services/api';

const today = () => new Date().toISOString().slice(0, 10);

const formatDate = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('vi-VN');
};

const SupplyDemandsPage = () => {
  const navigate = useNavigate();
  const [demands, setDemands] = useState([]);
  const [contracts, setContracts] = useState([]);
  const [farms, setFarms] = useState([]);
  const [crops, setCrops] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({
    supply_contract_id: '',
    farm_id: '',
    crop_id: '',
    quantity: '',
    unit: 'kg',
    frequency: 'once',
    target_date: today(),
    notes: '',
  });

  const loadData = async () => {
    setLoading(true);
    setError(null);

    try {
      const [demandsResponse, contractsResponse, farmsResponse, cropsResponse] = await Promise.all([
        api.get('/supply-demands'),
        api.get('/supply-contracts'),
        api.get('/farms'),
        api.get('/crops'),
      ]);
      setDemands(demandsResponse.data.data || demandsResponse.data || []);
      setContracts(contractsResponse.data.data || contractsResponse.data || []);
      setFarms(farmsResponse.data.data || farmsResponse.data || []);
      setCrops(cropsResponse.data.data || cropsResponse.data || []);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải nhu cầu cung ứng.');
      console.error('Error loading supply demands:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleContractChange = (contractId) => {
    const contract = contracts.find((item) => String(item.id) === String(contractId));
    setForm((prev) => ({
      ...prev,
      supply_contract_id: contractId,
      farm_id: contract?.farm_id || prev.farm_id,
      crop_id: contract?.crop_id || prev.crop_id,
      quantity: contract?.quantity || prev.quantity,
      unit: contract?.unit || prev.unit,
      frequency: contract?.frequency || prev.frequency,
    }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const payload = {
        ...form,
        quantity: parseFloat(form.quantity),
        status: 'pending',
      };

      Object.keys(payload).forEach((key) => {
        if (payload[key] === '' || payload[key] === undefined || payload[key] === null) delete payload[key];
      });

      const response = await api.post('/supply-demands', payload);
      setDemands((prev) => [response.data.data, ...prev]);
      setShowForm(false);
      setForm({
        supply_contract_id: '',
        farm_id: '',
        crop_id: '',
        quantity: '',
        unit: 'kg',
        frequency: 'once',
        target_date: today(),
        notes: '',
      });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tạo nhu cầu cung ứng.');
      console.error('Error creating supply demand:', err);
    } finally {
      setSaving(false);
    }
  };

  const planFromDemand = (demand) => {
    const params = new URLSearchParams({
      source: 'demand',
      supply_demand_id: String(demand.id),
      supply_contract_id: demand.supply_contract_id ? String(demand.supply_contract_id) : '',
      farm_id: String(demand.farm_id || ''),
      crop_id: String(demand.crop_id || ''),
      quantity: String(demand.quantity || ''),
      unit: demand.unit || 'kg',
      frequency: demand.frequency || 'once',
      target_date: demand.target_date?.slice(0, 10) || today(),
    });

    navigate(`/operations/planning?${params.toString()}`);
  };

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-gray-800">Nhu cầu cung ứng</h1>
          <p className="text-gray-600">Nhập nhu cầu giao hàng cụ thể để tính kế hoạch sản xuất.</p>
        </div>
        <button
          type="button"
          onClick={() => setShowForm((value) => !value)}
          className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
        >
          {showForm ? 'Đóng form' : 'Tạo nhu cầu'}
        </button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>}

      {showForm && (
        <div className="mb-6 bg-white rounded-lg shadow border border-gray-200 p-6">
          <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Hợp đồng liên kết</label>
              <select
                value={form.supply_contract_id}
                onChange={(event) => handleContractChange(event.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
              >
                <option value="">Nhu cầu lẻ, không thuộc hợp đồng</option>
                {contracts.map((contract) => (
                  <option key={contract.id} value={contract.id}>
                    #{contract.id} - {contract.customer_name} - {contract.crop?.name || 'cây trồng'}
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Farm</label>
              <select
                value={form.farm_id}
                onChange={(event) => setForm((prev) => ({ ...prev, farm_id: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
              >
                <option value="">Theo farm của tài khoản</option>
                {farms.map((farm) => (
                  <option key={farm.id} value={farm.id}>{farm.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Cây trồng</label>
              <select
                value={form.crop_id}
                onChange={(event) => setForm((prev) => ({ ...prev, crop_id: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
                required
              >
                <option value="">Chọn cây</option>
                {crops.map((crop) => (
                  <option key={crop.id} value={crop.id}>{crop.name}</option>
                ))}
              </select>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                <input
                  type="number"
                  step="any"
                  min="0.01"
                  value={form.quantity}
                  onChange={(event) => setForm((prev) => ({ ...prev, quantity: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Đơn vị</label>
                <select
                  value={form.unit}
                  onChange={(event) => setForm((prev) => ({ ...prev, unit: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
                >
                  <option value="kg">kg</option>
                  <option value="trái">trái</option>
                  <option value="bó">bó</option>
                  <option value="thùng">thùng</option>
                </select>
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tần suất</label>
              <select
                value={form.frequency}
                onChange={(event) => setForm((prev) => ({ ...prev, frequency: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
              >
                <option value="once">Một lần</option>
                <option value="daily">Mỗi ngày</option>
                <option value="weekly">Mỗi tuần</option>
                <option value="monthly">Mỗi tháng</option>
                <option value="seasonal">Theo mùa</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Ngày giao mục tiêu</label>
              <input
                type="date"
                value={form.target_date}
                onChange={(event) => setForm((prev) => ({ ...prev, target_date: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                required
              />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
              <textarea
                value={form.notes}
                onChange={(event) => setForm((prev) => ({ ...prev, notes: event.target.value }))}
                rows="3"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>
            <div className="md:col-span-2">
              <button
                type="submit"
                disabled={saving}
                className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50"
              >
                {saving ? 'Đang lưu...' : 'Lưu nhu cầu'}
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-gray-600">Đang tải nhu cầu...</div>
        ) : demands.length === 0 ? (
          <div className="p-8 text-center text-gray-600">Chưa có nhu cầu cung ứng.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nguồn</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cây</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nhu cầu</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày giao</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {demands.map((demand) => (
                  <tr key={demand.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <div className="font-medium text-gray-900">{demand.contract?.customer_name || 'Nhu cầu lẻ'}</div>
                      <div className="text-xs text-gray-500">{demand.supply_contract_id ? `HĐ #${demand.supply_contract_id}` : 'Không thuộc hợp đồng'}</div>
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-700">{demand.crop?.name || '-'}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{Number(demand.quantity).toLocaleString('vi-VN')} {demand.unit}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{formatDate(demand.target_date)}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{demand.status || '-'}</td>
                    <td className="px-4 py-3">
                      <button
                        type="button"
                        onClick={() => planFromDemand(demand)}
                        className="text-emerald-700 hover:text-emerald-900 font-medium text-sm"
                      >
                        Tính kế hoạch
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

export default SupplyDemandsPage;

