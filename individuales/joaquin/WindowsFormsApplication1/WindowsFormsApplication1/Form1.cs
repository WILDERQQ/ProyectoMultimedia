using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Drawing.Imaging;
// Para conectar a SQLServer
using System.Data.SqlClient;

namespace WindowsFormsApplication1
{
    public partial class Form1 : Form
    {
        int cR, cG, cB;
        int cwR, cwG, cwB;
	    int ventana;

        double varRw, varGw, varBw;

        public Form1()
        {
            InitializeComponent();

        }

        private void button1_Click(object sender, EventArgs e)
        {
            openFileDialog1.ShowDialog();
            Bitmap bmp = new Bitmap(openFileDialog1.FileName);
            pictureBox1.Image = bmp;
            pictureBox2.Image = bmp;
        }

        private void pictureBox1_MouseClick(object sender, MouseEventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Color c = new Color();
            c = bmp.GetPixel(e.X, e.Y);
            textBox1.Text = c.R.ToString();
            textBox2.Text = c.G.ToString();
            textBox3.Text = c.B.ToString();
            cR = c.R;
            cB = c.B;
            cG = c.G;

            cwR = 0; cwG = 0; cwB = 0;

            // PROMEDIO RGB
            for (int i = e.X - (ventana / 2); i < e.X + (ventana / 2);i++ ) {
                for (int j = e.Y - (ventana / 2); j < e.Y + (ventana / 2); j++) {
                    c = bmp.GetPixel(i,j);
                    cwR = cwR + c.R;
                    cwG = cwG + c.G;
                    cwB = cwB + c.B;

                }
            }
            // cwR = cwR / 100;
            // cwG = cwG / 100;
            // cwB = cwB / 100;

            cwR = cwR / (ventana * ventana);
            cwG = cwG / (ventana * ventana);
            cwB = cwB / (ventana * ventana);
            textBox1.Text = textBox1.Text;
            textBox2.Text = textBox2.Text;
            textBox3.Text = textBox3.Text;

            // VARIANZA
            varRw = 0; varGw = 0; varBw = 0;

            for (int i = e.X - (ventana / 2); i < e.X + (ventana / 2); i++){
                for (int j = e.Y - (ventana / 2); j < e.Y + (ventana / 2); j++){
                    c = bmp.GetPixel(i, j);

                    varRw += Math.Pow(c.R - cwR, 2);
                    varGw += Math.Pow(c.G - cwG, 2);
                    varBw += Math.Pow(c.B - cwB, 2);
                }
            }

            varRw = varRw / (ventana * ventana);
            varGw = varGw / (ventana * ventana);
            varBw = varBw / (ventana * ventana);

            //textBox1.Text = textBox1.Text + " " + cwR.ToString();
            //textBox2.Text = textBox2.Text + " " + cwG.ToString();
            //textBox3.Text = textBox3.Text + " " + cwB.ToString();
            textBox5.Text =
                "PR:" + cwR +
                " PG:" + cwG +
                " PB:" + cwB +
                " VR:" + ((int)varRw).ToString();

        }

        private void button2_Click(object sender, EventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Color c = new Color();
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);
            for (int i = 0; i < bmp.Width; i++){
                for (int j = 0; j < bmp.Height; j++)
                {
                    c = bmp.GetPixel(i,j);
                    bmp2.SetPixel(i, j, Color.FromArgb(c.R,0,0));
                }
            }
            pictureBox2.Image = bmp2;
        }

        private void button3_Click(object sender, EventArgs e)
        {

            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Color c = new Color();
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);
            for (int i = 0; i < bmp.Width; i++)
            {
                for (int j = 0; j < bmp.Height; j++)
                {
                    c = bmp.GetPixel(i, j);
                    bmp2.SetPixel(i, j, Color.FromArgb(0, c.G, 0));
                }
            }
            pictureBox2.Image = bmp2;

        }

        private void button4_Click(object sender, EventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Color c = new Color();
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);
            for (int i = 0; i < bmp.Width; i++)
            {
                for (int j = 0; j < bmp.Height; j++)
                {
                    c = bmp.GetPixel(i, j);
                    if ((c.R == cR) && (c.G == cG) && (c.B == cB))
                    {
                        bmp2.SetPixel(i, j, Color.FromArgb(0, 0, 0));
                    }
                    else
                    { bmp2.SetPixel(i, j, Color.FromArgb(c.R, c.G, c.B)); }
                    
                }
            }
            pictureBox2.Image = bmp2;
        }

        


        private void button5_Click(object sender, EventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Color c = new Color();
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);
            for (int i = 0; i < bmp.Width; i++)
            {
                for (int j = 0; j < bmp.Height; j++)
                {
                    c = bmp.GetPixel(i, j);
                    if ((cR - 10 < c.R) && (c.R < cR + 10) && (cG - 10 < c.G) && (c.G < cG + 10) && (cB - 10 < c.B) && (c.B < cB + 10))
                    {
                        bmp2.SetPixel(i, j, Color.FromArgb(0, 0, 0));
                    }
                    else
                    { bmp2.SetPixel(i, j, Color.FromArgb(c.R, c.G, c.B)); }

                }
            }
            pictureBox2.Image = bmp2;
        }

       
        //Deteccion de texturas 
        private void button6_Click(object sender, EventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Color c = new Color();
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);
            int cRm, cGm, cBm;
            for (int i = 0; i < bmp.Width - ventana; i = i + ventana)
            {
                for (int j = 0; j < bmp.Height - ventana; j = j + ventana)
                {
                    cRm = 0;
                    cGm = 0;
                    cBm = 0;
                for (int k=i; k<i+ventana;k++){
		            for (int l = j; l < j + ventana; l++) {
			                c = bmp.GetPixel(k, l);
                    	    cRm = cRm + c.R;
                    	    cGm = cGm + c.G;
                    	    cBm = cBm + c.B;
                    }
		        }   
                    cRm = cRm / (ventana * ventana);
                    cGm = cGm / (ventana * ventana);
                    cBm = cBm / (ventana * ventana);
                    if ((cwR - 10 < cRm) && (cRm < cwR + 10) && (cwG - 10 < cGm) && (cGm < cwG + 10) && (cwB - 10 < cBm) && (cBm < cwB + 10))
                    {
                        for (int k=i; k<i+ventana; k++)
                            for (int l = j; l < j + ventana; l++)
                            {
                                bmp2.SetPixel(k, l, Color.FromArgb(0, 0, 0));
                            }
                    }
                    else
                    {
                        for (int k = i; k < i + ventana; k++)
                            for (int l = j; l < j + ventana; l++)
                            {
                                c = bmp.GetPixel(k, l);
                                bmp2.SetPixel(k, l, Color.FromArgb(c.R, c.G, c.B));
                            }
                    }
                }
            }
            pictureBox2.Image = bmp2;
        }

        private void Form1_Load_1(object sender, EventArgs e)
        {
            ventana = 10;	
        }

        private void button7_Click(object sender, EventArgs e)
        {
            String sql;
            SqlConnection con = new SqlConnection();
            con.ConnectionString = "server=(local); database=multi; integrated security=true";

            sql = "insert into texturas(r,g,b,descripcion) ";
            sql = sql + "values(" + int.Parse(textBox1.Text) + "," 
                              + int.Parse(textBox2.Text) + "," 
                              + int.Parse(textBox3.Text) + ",'')";

            SqlCommand cmd = new SqlCommand();
            cmd.Connection = con;
            cmd.CommandText = sql;

            con.Open();
            cmd.ExecuteNonQuery();
            con.Close();
        }

        private void button8_Click(object sender, EventArgs e)
        {
            String sql;
            SqlConnection con = new SqlConnection();
            con.ConnectionString = "server=(local); database=multi; integrated security=true";

            sql = "select avg(r) as pr, avg(g) as pg, avg(b) as pb from texturas;";
            
            SqlCommand cmd = new SqlCommand();
            cmd.Connection = con;
            cmd.CommandText = sql;

            con.Open();
            SqlDataReader dr = cmd.ExecuteReader();

            if (dr.Read())
            {
                textBox4.Text = "R: " + dr["pr"].ToString() +
                                " G: " + dr["pg"].ToString() +
                                " B: " + dr["pb"].ToString();
            }

            con.Close();
        }
        //a. Clasificador de texturas
        private void button9_Click(object sender, EventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);

            Color c;

            int rProm = cwR; int gProm = cwG; int bProm = cwB;

            int tolerancia = 20;

            for (int i = 0; i < bmp.Width - ventana; i += ventana)
            {
                for (int j = 0; j < bmp.Height - ventana; j += ventana)
                {
                    int sumaR = 0;
                    int sumaG = 0;
                    int sumaB = 0;

                    // Promedio del bloque
                    for (int k = i; k < i + ventana; k++)
                    {
                        for (int l = j; l < j + ventana; l++)
                        {
                            c = bmp.GetPixel(k, l);

                            sumaR += c.R;
                            sumaG += c.G;
                            sumaB += c.B;
                        }
                    }

                    int promR = sumaR / (ventana * ventana);
                    int promG = sumaG / (ventana * ventana);
                    int promB = sumaB / (ventana * ventana);

                    bool textura =
                        Math.Abs(promR - rProm) < tolerancia &&
                        Math.Abs(promG - gProm) < tolerancia &&
                        Math.Abs(promB - bProm) < tolerancia;

                    for (int k = i; k < i + ventana; k++)
                    {
                        for (int l = j; l < j + ventana; l++)
                        {
                            if (textura)
                            {
                                bmp2.SetPixel(k, l, Color.Black);
                            }
                            else
                            {
                                c = bmp.GetPixel(k, l);
                                bmp2.SetPixel(k, l, c);
                            }
                        }
                    }
                }
            }

            pictureBox2.Image = bmp2;
        }
        //b. Suavizador de texturas 3x3 
        private void button10_Click(object sender, EventArgs e)
        {
            Bitmap bmp = new Bitmap(pictureBox1.Image);
            Bitmap bmp2 = new Bitmap(bmp.Width, bmp.Height);

            Color c;

            for (int x = 1; x < bmp.Width - 1; x++)
            {
                for (int y = 1; y < bmp.Height - 1; y++)
                {
                    int sumaR = 0;
                    int sumaG = 0;
                    int sumaB = 0;

                    // Ventana 3x3
                    for (int i = -1; i <= 1; i++)
                    {
                        for (int j = -1; j <= 1; j++)
                        {
                            c = bmp.GetPixel(x + i, y + j);

                            sumaR += c.R;
                            sumaG += c.G;
                            sumaB += c.B;
                        }
                    }

                    int promR = sumaR / 9;
                    int promG = sumaG / 9;
                    int promB = sumaB / 9;

                    bmp2.SetPixel(x, y, Color.FromArgb(promR, promG, promB));
                }
            }

            pictureBox2.Image = bmp2;
        }

    }
}
